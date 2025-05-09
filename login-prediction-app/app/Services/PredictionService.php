<?php

namespace App\Services;

use App\Models\User;
use DateTime;

/**
 * Tahmin algoritmalarını içeren servis sınıfı
 */
class PredictionService
{
    private $user;
    
    /**
     * Constructor
     * 
     * @param User $user Kullanıcı nesnesi
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    /**
     * Algoritma 1: Ortalama Aralık Yöntemi
     * 
     * Bu algoritma, kullanıcının önceki login'leri arasındaki ortalama süreyi hesaplar
     * ve son login zamanına bu süreyi ekleyerek bir sonraki tahmini login zamanını bulur.
     * 
     * @return DateTime|null Tahmini bir sonraki login zamanı veya tahmin yapılamazsa null
     */
    public function predictByAverageInterval()
    {
        $lastLogin = $this->user->getLastLogin();
        $avgInterval = $this->user->getAverageLoginInterval();
        
        if ($lastLogin === null || $avgInterval === null) {
            return null;
        }
        
        $prediction = clone $lastLogin;
        $prediction->modify('+' . round($avgInterval) . ' hours');
        
        return $prediction;
    }
    
    /**
     * Algoritma 2: Gün ve Saat Paterni Yöntemi
     * 
     * Bu algoritma, kullanıcının en sık login yaptığı gün ve saati belirler
     * ve bir sonraki bu gün ve saatte login yapacağını tahmin eder.
     * 
     * @return DateTime|null Tahmini bir sonraki login zamanı veya tahmin yapılamazsa null
     */
    public function predictByDayHourPattern()
    {
        $lastLogin = $this->user->getLastLogin();
        if ($lastLogin === null) {
            return null;
        }
        
        // En sık login yapılan günü bul
        $dayDistribution = $this->user->getLoginDayDistribution();
        $mostFrequentDay = array_search(max($dayDistribution), $dayDistribution);
        
        // En sık login yapılan saati bul
        $hourDistribution = $this->user->getLoginHourDistribution();
        $mostFrequentHour = array_search(max($hourDistribution), $hourDistribution);
        
        // Şu anki zamanı al
        $now = new DateTime();
        $prediction = clone $now;
        
        // Bir sonraki en sık login yapılan güne ayarla
        $currentDay = (int)$now->format('N');
        $daysToAdd = ($mostFrequentDay - $currentDay + 7) % 7;
        if ($daysToAdd === 0) {
            // Eğer bugün en sık login yapılan günse ve saat geçmişse, bir sonraki haftaya ayarla
            if ((int)$now->format('G') >= $mostFrequentHour) {
                $daysToAdd = 7;
            }
        }
        
        $prediction->modify('+' . $daysToAdd . ' days');
        
        // Saati ayarla
        $prediction->setTime($mostFrequentHour, 0, 0);
        
        return $prediction;
    }
    
    /**
     * Algoritma 3: Ağırlıklı Son Login Yöntemi
     * 
     * Bu algoritma, son login'lere daha fazla ağırlık vererek bir sonraki login zamanını tahmin eder.
     * Son login'lerin daha iyi bir gösterge olduğu varsayımına dayanır.
     * 
     * @return DateTime|null Tahmini bir sonraki login zamanı veya tahmin yapılamazsa null
     */
    public function predictByWeightedRecent()
    {
        $logins = $this->user->getLoginDates();
        if (count($logins) < 3) {
            return $this->predictByAverageInterval(); // Yeterli veri yoksa basit yönteme geri dön
        }
        
        $lastLogin = $this->user->getLastLogin();
        if ($lastLogin === null) {
            return null;
        }
        
        // Son 3 login aralığını hesapla ve ağırlıklandır
        $intervals = [];
        $weights = [0.5, 0.3, 0.2]; // Son aralığa %50, öncekine %30, daha öncekine %20 ağırlık Bunlar tamamen Senesozdel tercihlerine bağlıdır
        
        $count = count($logins);
        for ($i = $count - 1; $i >= max(1, $count - 3); $i--) {
            $interval = $logins[$i]->getTimestamp() - $logins[$i-1]->getTimestamp();
            $intervals[] = $interval / 3600; // Saniyeyi saate çevir
        }
        
        // Ağırlıklı ortalama hesapla
        $weightedSum = 0;
        $totalWeight = 0;
        
        for ($i = 0; $i < count($intervals); $i++) {
            $weightedSum += $intervals[$i] * $weights[$i];
            $totalWeight += $weights[$i];
        }
        
        $weightedAvg = $weightedSum / $totalWeight;
        
        // Tahmini hesapla
        $prediction = clone $lastLogin;
        $prediction->modify('+' . round($weightedAvg) . ' hours');
        
        return $prediction;
    }
    
    /**
     * Algoritma 4: Günlük Rutin Yöntemi
     * 
     * Bu algoritma, kullanıcının günlük rutinini analiz eder ve her gün için
     * en olası login saatini belirleyerek tahmin yapar.
     * 
     * @return DateTime|null Tahmini bir sonraki login zamanı veya tahmin yapılamazsa null
     */
    public function predictByDailyRoutine()
    {
        $lastLogin = $this->user->getLastLogin();
        $logins = $this->user->getLoginDates();
        
        if ($lastLogin === null || count($logins) < 5) {
            return null;
        }
        
        // Her gün için login saatlerini topla
        $dailyHours = [
            1 => [], // Pazartesi
            2 => [], // Salı
            3 => [], // Çarşamba
            4 => [], // Perşembe
            5 => [], // Cuma
            6 => [], // Cumartesi
            7 => []  // Pazar
        ];
        
        foreach ($logins as $login) {
            $day = (int)$login->format('N');
            $hour = (int)$login->format('G');
            $dailyHours[$day][] = $hour;
        }
        
        // Şu anki zamanı al
        $now = new DateTime();
        $currentDay = (int)$now->format('N');
        
        // Bir sonraki günden başlayarak 7 gün boyunca kontrol et
        for ($i = 1; $i <= 7; $i++) {
            $checkDay = ($currentDay + $i) % 7;
            if ($checkDay === 0) $checkDay = 7; // 0 yerine 7 kullan (Pazar)
            
            // Bu gün için login saatleri varsa
            if (!empty($dailyHours[$checkDay])) {
                // Ortalama login saatini hesapla
                $avgHour = array_sum($dailyHours[$checkDay]) / count($dailyHours[$checkDay]);
                
                // Tahmini oluştur
                $prediction = clone $now;
                $prediction->modify('+' . $i . ' days');
                $prediction->setTime(round($avgHour), 0, 0);
                
                return $prediction;
            }
        }
        
        // Hiçbir gün için veri bulunamazsa null döndür
        return null;
    }
    
    /**
     * Tüm algoritmaları çalıştırır ve sonuçları döndürür
     * 
     * @return array Algoritma adı => tahmin edilen DateTime şeklinde sonuçlar
     */
    public function getAllPredictions()
    {
        return [
            'Ortalama Aralık Yöntemi' => $this->predictByAverageInterval(),
            'Gün ve Saat Paterni Yöntemi' => $this->predictByDayHourPattern(),
            'Ağırlıklı Son Login Yöntemi' => $this->predictByWeightedRecent(),
            'Günlük Rutin Yöntemi' => $this->predictByDailyRoutine()
        ];
    }
}