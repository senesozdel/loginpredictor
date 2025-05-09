<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    private $apiUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiUrl = config('services.login_api.url');
    }

    /**
     * API'den tüm kullanıcı login verilerini çeker
     * 
     * @return array Kullanıcı verileri dizisi
     * @throws Exception API hatası durumunda
     */
    public function getDataRows()
    {
        try {
            $response = Http::withOptions([
                'verify' => false, 
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->apiUrl);

            
            if ($response->failed()) {
                throw new Exception("API isteği başarısız oldu: " . $response->status());
            }

            $responseData = $response->json();

            // API yanıtının doğru formatta olup olmadığını kontrol et
            if (!isset($responseData['status']) || $responseData['status'] !== 0 || !isset($responseData['data']['rows'])) {
                throw new Exception("API'den beklenmeyen yanıt formatı alındı");
            }

            // Sadece kullanıcı verilerini içeren rows dizisini döndür
            return $responseData['data']['rows'];
        } catch (Exception $e) {
            Log::error('API Hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * API'den belirli bir kullanıcının verilerini çeker
     * 
     * @param string $userId Kullanıcı ID
     * @return array Kullanıcı verisi
     * @throws Exception API hatası durumunda
     */
    public function getUserById($userId)
    {
        try {
            // Tüm kullanıcı verilerini getir
            $allUsers = $this->getDataRows();
            
            // ID'ye göre kullanıcıyı bul
            foreach ($allUsers as $user) {
                if (isset($user['id']) && $user['id'] === $userId) {
                    return $user;
                }
            }
            
            // Kullanıcı bulunamadı
            throw new Exception("ID'si {$userId} olan kullanıcı bulunamadı");
            
        } catch (Exception $e) {
            Log::error('API Hatası: ' . $e->getMessage());
            throw $e;
        }
    }

     /**
     * Tüm kullanıcıların  kullanıcı adlarını ve Idlerini döndürür
     * 
     * @return array Kullanıcı adları dizisi
     * @throws Exception API hatası durumunda
     */
    public function getAllUserNames()
    {
        try {
            $allUsers = $this->getDataRows();
            $userNames = [];
            
            foreach ($allUsers as $user) {
                if (isset($user['id']) && isset($user['name'])) {
                    $userNames[] = [
                        'id' => $user['id'],
                        'name' => $user['name']
                    ];
                }
            }
            
            return $userNames;
        } catch (Exception $e) {
            Log::error('API Kullanıcı Adları Hatası: ' . $e->getMessage());
            throw $e;
        }
    }

}