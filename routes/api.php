<?php

use App\Http\Controllers\dashboard_instansi;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\WebProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::get('/noauth', [AuthController::class, 'nologin'])->name("throwUser");
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    Route::group(['prefix' => '/dashboard/i'], function () {
        Route::get('/', [dashboard_instansi::class, 'index']);
        Route::get('/getActivityReport', [dashboard_instansi::class, 'getActivityReport']);
        Route::get('/getProfileInfo', [dashboard_instansi::class, 'getProfileInfo']);
        Route::post('/getEskulInstansi', [dashboard_instansi::class, 'getEskulInstansi']);
    });
    Route::group(['prefix' => '/webProfile'], function () {

        Route::post('/store', [WebProfileController::class, 'store']);
    });
    Route::group(['prefix' => '/eskul'], function () {

        Route::post('/trash', [EskulController::class, 'trash']);
        Route::post('/store', [EskulController::class, 'store']);
        Route::post('/restore', [EskulController::class, 'restore']);
    });

    // Protected routes
    Route::get('/users', [AuthController::class, 'profiles']);
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/getauth', [AuthController::class, 'getAuth']);
});

