<?php

use App\Http\Controllers\EskulAbsentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\dashboard_instansi;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\WebProfileController;
use App\Http\Controllers\OrgsWebPageController;
use App\Http\Controllers\EskulReportActivityController;
use App\Http\Controllers\DashboardOrganizationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::get('/noauth', [AuthController::class, 'nologin'])->name("throwUser");
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/auth/googleSignIn', [AuthController::class, 'googleSignIn']);

Route::group(['prefix' => '/apps'], function () {
    Route::get('/getAppStats', [AppsController::class, 'GetAppsStats']);
});


Route::group(['prefix' => '/webprofile'], function () {
    Route::post('/getProfileInfoWithDomain', [dashboard_instansi::class, 'getProfileInfoWithDomain']);
    Route::post('/getEskulInstansiPublic', [EskulController::class, 'getEskulInstansiPublic']);
});

Route::post('/getEskulWebPageUrl', [OrgsWebPageController::class, 'getEskulWebPageUrl']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/absent/getUserByName', [EskulAbsentController::class, 'getUserByName']);
    Route::post('/absent/storeAbsen', [EskulAbsentController::class, 'storeAbsen']);
    Route::get('/absent/getEskulAbsent', [EskulAbsentController::class, 'GetEskulAbsen']);
    Route::post('/absent/getEskulAbsenByCode', [EskulAbsentController::class, 'GetEskulAbsenByCode']);
    Route::post('/absent/editAbsen', [EskulAbsentController::class, 'editAbsen']);
    Route::post('/absent/deleteAbsen', [EskulAbsentController::class, 'deleteAbsen']);

    Route::group(['prefix' => '/dashboard/o'], function () {
        Route::get('/getProfileInfo', [DashboardOrganizationController::class, 'getProfileInfo']);
        Route::get('/getEskulMembers', [DashboardOrganizationController::class, 'getEskulMembers']);
        Route::post('/storeEskulMember', [DashboardOrganizationController::class, 'storeEskulMember']);

        Route::apiResource('eskul-report-activities', EskulReportActivityController::class);

        Route::group(['prefix' => '/webprofile'], function () {
            Route::post('/storeNavbar', [OrgsWebPageController::class, 'storeNavbarWebpage']);
            Route::post('/storeJumbotron', [OrgsWebPageController::class, 'storeJumbotronWebpage']);
            Route::post('/storeAboutUs', [OrgsWebPageController::class, 'storeAboutUsWebpage']);
            Route::post('/getEskulWebPage', [OrgsWebPageController::class, 'getEskulWebPage']);
            Route::post('/storeActivitiesEskulItem', [OrgsWebPageController::class, 'storeActivitiesEskulItem']);
            Route::post('/storeActivitiesDesc', [OrgsWebPageController::class, 'storeActivitiesDesc']);
            Route::post('/storeGallery', [OrgsWebPageController::class, 'storeGallery']);
        });
    });
    Route::group(['prefix' => '/dashboard/i'], function () {
        Route::get('/getMasterHakAkses', [HakAksesController::class, 'getMasterHakAkses']);
        Route::post('/updateHakAkses', [HakAksesController::class, 'updateHakAkses']);
        Route::post('/addUser', [AuthController::class, 'addUser']);
        Route::post('/editUser', [AuthController::class, 'editUser']);
        Route::get('/', [dashboard_instansi::class, 'index']);
        Route::get('/getActivityReport', [dashboard_instansi::class, 'getActivityReport']);
        Route::get('/getProfileInfo', [dashboard_instansi::class, 'getProfileInfo']);

        Route::post('/getEskulInstansi', [EskulController::class, 'getEskulInstansi']);
        Route::post('/getUserInstansi', [dashboard_instansi::class, 'getUserInstansi']);
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

