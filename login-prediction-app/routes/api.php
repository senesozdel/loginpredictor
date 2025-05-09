<?php

use App\Http\Controllers\Api\PredictionApiController;
use Illuminate\Support\Facades\Route;

Route::get('users', [PredictionApiController::class, 'getAllUsers']);
Route::get('usernames', [PredictionApiController::class, 'getAllUserNames']);
Route::get('usernames', [PredictionApiController::class, 'getAllUserNames']); // Yeni endpoint
Route::get('users/{userId}/predictions', [PredictionApiController::class, 'getPredictions']);
