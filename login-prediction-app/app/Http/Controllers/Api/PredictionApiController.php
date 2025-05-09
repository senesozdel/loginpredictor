<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\ApiService;
use App\Services\PredictionService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     title="Login Prediction API",
 *     version="1.0.0",
 *     description="Kullanıcıların login tahminleri için API"
 * )
 */
class PredictionApiController extends Controller
{
    protected $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Tüm kullanıcıları listeler",
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function getAllUsers()
    {
        try {
            $rows = $this->apiService->getDataRows();
            return response()->json(['data' => $rows]);
        } catch (Exception $e) {
            Log::error('API Kullanıcı Listesi Hatası: ' . $e->getMessage());
            return response()->json(['error' => 'Kullanıcılar alınırken bir hata oluştu'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/usernames",
     *     tags={"Users"},
     *     summary="Tüm kullanıcı adlarını listeler",
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getAllUserNames()
    {
        try {
            $userNames = $this->apiService->getAllUserNames();
            return response()->json(['data' => $userNames]);
        } catch (Exception $e) {
            Log::error('API Kullanıcı Adları Listesi Hatası: ' . $e->getMessage());
            return response()->json(['error' => 'Kullanıcı adları alınırken bir hata oluştu'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}",
     *     tags={"Users"},
     *     summary="Belirli bir kullanıcıyı getirir",
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı")
     * )
     */
    public function getUserById($userId)
    {
        try {
            $userData = $this->apiService->getUserById($userId);
            return response()->json($userData);
        } catch (Exception $e) {
            Log::error('API Kullanıcı Detay Hatası: ' . $e->getMessage());
            return response()->json(['error' => 'Kullanıcı alınırken bir hata oluştu'], 500);
}
    }

    /**
     * @OA\Get(
     *     path="/api/users/{userId}/predictions",
     *     tags={"Predictions"},
     *     summary="Kullanıcı için login tahminlerini getirir",
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="Kullanıcı ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(
     *             @OA\Property(property="userId", type="string"),
     *             @OA\Property(
     *                 property="predictions",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="string",
     *                     format="date-time",
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kullanıcı bulunamadı")
     * )
     */
    public function getPredictions($userId)
    {
        try {
            // API'den IDye göre kullanıcı verilerini çek
            $userData = $this->apiService->getUserById($userId);
            
            // Yeni User modeli oluştur
            $user = new User([
                'id' => $userData['id'],
                'name' => $userData['name'],
                'logins' => $userData['logins'],
            ]);
            
            // Tahmin servisi oluştur
            $predictionService = new PredictionService($user);

              // Kullanıcının son login zamanı
            $lastLogin = $user->getLastLogin();
            $lastLoginTime = $lastLogin ? $lastLogin->format('Y-m-d H:i:s') : null;
            
            // Tahminleri al
            $predictions = $predictionService->getAllPredictions();
            
            // DateTime nesnelerini formatla
            $formattedPredictions = [];
            foreach ($predictions as $algorithm => $prediction) {
                $formattedPredictions[$algorithm] = $prediction ? $prediction->format('Y-m-d H:i:s') : null;
            }
            
            return response()->json([
                'userId' => $userId,
                'predictions' => $formattedPredictions,
                'lastLogin' => $lastLoginTime
            ]);
        } catch (Exception $e) {
            // Hata durumunda log kaydı oluştur. Storage/logs/laravel.log dosyasına yazılır.
            Log::error('API Tahmin Hatası: ' . $e->getMessage());
            return response()->json(['error' => 'Tahminler alınırken bir hata oluştu'], 500);
        }
    }
}