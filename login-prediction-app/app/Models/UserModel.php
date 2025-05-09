<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(
 *         property="logins",
 *         type="array",
 *         @OA\Items(type="string", format="date-time")
 *     )
 * )
 */
class UserModel
{
    // Bu sınıf sadece Swagger dokümantasyonu için kullanılıyor
}