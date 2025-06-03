<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Ecole\ClasseController;
use App\Http\Controllers\Api\Ecole\SerieController;
use App\Http\Controllers\Api\Ecole\SubjectController;
use App\Http\Controllers\Api\Profile\UserController;
use App\Http\Controllers\Api\Resource\ResourceController;
use App\Http\Controllers\Other\ParrainageController;
use App\Http\Controllers\Other\QuotaController;
use Illuminate\Support\Facades\Route;









/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('v1')->group(function () {

    // Routes publiques
    Route::prefix('ressources')->group(function () {
        Route::get('/', [ResourceController::class, 'index']);
        Route::get('{id}', [ResourceController::class, 'show']);
    });

    // Routes protégées (authentification requise)
    Route::middleware('auth:api')->prefix('ressources')->group(function () {
        Route::post('/', [ResourceController::class, 'store']);
        Route::get('{id}/download', [ResourceController::class, 'download']);
        Route::delete('{id}', [ResourceController::class, 'destroy']);
    });

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
        Route::post('reset-password',  [AuthController::class, 'resetPassword']);

        Route::middleware('auth.api')->group(function () {
            Route::post('logout',  [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me',       [AuthController::class, 'me']);
        });
    });

    // Utilisateur connecté
    Route::middleware('auth:api')->prefix('me')->group(function () {
        Route::get('/',       [UserController::class, 'me']);
        Route::put('/',       [UserController::class, 'update']);
        Route::delete('/',    [UserController::class, 'destroy']);

        Route::post('change-password', [UserController::class, 'changePassword']);

        Route::post('avatar',   [UserController::class, 'uploadAvatar']);
        Route::delete('avatar', [UserController::class, 'deleteAvatar']);
        Route::get('quota', [QuotaController::class, 'showQuota']);
        Route::post('parrainage', [ParrainageController::class, 'utiliserCodeParrain']);
        // 📜 Historique d’actions
        Route::get('actions-history', [UserController::class, 'getActionsHistory']);
    });

    // Matières, séries, classes (GET)
    Route::get('subjects', [SubjectController::class, 'index']);
    Route::get('subjects/{id}', [SubjectController::class, 'show']);
    Route::get('series', [SerieController::class, 'index']);
    Route::get('series/{id}', [SerieController::class, 'show']);
    Route::get('classes', [ClasseController::class, 'index']);
    Route::get('classes/{id}', [ClasseController::class, 'show']);

    // Matières, séries, classes (CRUD sauf index/show)
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('subjects', SubjectController::class)->except(['index', 'show']);
        Route::apiResource('series', SerieController::class)->except(['index', 'show']);
        Route::apiResource('classes', ClasseController::class)->except(['index', 'show']);
    });
});
