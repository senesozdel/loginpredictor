<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    
    /**
     *  Bu özellikler açık olarak doldurulabilir 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'logins',
    ];

    /**
     * Tarih formatında saklanacak özellikler
     */
    protected $dates = [];

    /**
     * Login zamanlarını Carbon nesnelerine dönüştürür
     * 
     * @return array Carbon nesneleri dizisi
     */
    public function getLoginDates()
    {
        $loginDates = [];
        
        if (isset($this->logins) && is_array($this->logins)) {
            foreach ($this->logins as $login) {
                $loginDates[] = Carbon::parse($login);
            }
            
            // Login zamanlarını sıralar
            usort($loginDates, function($a, $b) {
                return $a <=> $b;
            });
        }
        
        return $loginDates;
    }
    
    /**
     * Kullanıcının login günlerini döndürür (Pazartesi: 1, Pazar: 7)
     * 
     * @return array Gün bazında login sayıları
     */
    public function getLoginDayDistribution()
    {
        $days = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
        
        foreach ($this->getLoginDates() as $login) {
            $dayOfWeek = (int)$login->format('N'); // ISO-8601 formatında gün (1-7)
            $days[$dayOfWeek]++;
        }
        
        return $days;
    }
    
    /**
     * Kullanıcının login saatlerini döndürür (0-23)
     * 
     * @return array Saat bazında login sayıları
     */
    public function getLoginHourDistribution()
    {
        $hours = array_fill(0, 24, 0);
        
        foreach ($this->getLoginDates() as $login) {
            $hour = (int)$login->format('G'); // 24 saat formatında (0-23)
            $hours[$hour]++;
        }
        
        return $hours;
    }
    
    /**
     * İki login arasındaki ortalama süreyi hesaplar
     * 
     * @return float|null Ortalama süre (saat cinsinden) veya veri yoksa null
     */
    public function getAverageLoginInterval()
    {
        $loginDates = $this->getLoginDates();
        
        if (count($loginDates) < 2) {
            return null;
        }
        
        $totalIntervals = 0;
        $totalHours = 0;
        
        for ($i = 1; $i < count($loginDates); $i++) {
            $interval = $loginDates[$i]->getTimestamp() - $loginDates[$i-1]->getTimestamp();
            $hours = $interval / 3600; // Saniyeyi saate çevir
            $totalHours += $hours;
            $totalIntervals++;
        }
        
        return $totalHours / $totalIntervals;
    }
    
    /**
     * En son login zamanını döndürür
     * 
     * @return Carbon|null Son login zamanı veya login yoksa null
     */
    public function getLastLogin()
    {
        $loginDates = $this->getLoginDates();
        
        if (empty($loginDates)) {
            return null;
        }
        
        return end($loginDates);
    }
}