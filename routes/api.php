<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Ecole\ClasseController;
use App\Http\Controllers\Api\Ecole\SerieController;
use App\Http\Controllers\Api\Ecole\SubjectController;
use App\Http\Controllers\Api\Message\MessageController;
use App\Http\Controllers\Api\Other\KeywordController;
use App\Http\Controllers\Api\Other\LikeController;
use App\Http\Controllers\Api\Other\ParrainageController;
use App\Http\Controllers\Api\Other\QuotaController;
use App\Http\Controllers\Api\Profile\UserController;
use App\Http\Controllers\Api\Resource\ResourceController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // Authentification
    Route::prefix('auth')->group(function () {
        Route::post('email/send-code', [EmailVerificationController::class, 'sendCode']);
        Route::post('email/verify-code', [EmailVerificationController::class, 'verifyCode']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    Route::get('mots-cles/suggestions', [KeywordController::class, 'suggest']);

    // Ressources publiques
    Route::prefix('ressources')->group(function () {
        Route::get('/', [ResourceController::class, 'index']);
        Route::get('{id}', [ResourceController::class, 'show']);
        Route::get('/filter', [ResourceController::class, 'filter']);
        Route::get('{id}/messages', [MessageController::class, 'index']);
    });

    // Métadonnées publiques (matières, séries, classes)
    Route::apiResources([
        'subjects' => SubjectController::class,
        'series' => SerieController::class,
        'classes' => ClasseController::class,
    ], ['only' => ['index', 'show']]);

    Route::get('likes/count/{resourceId}', [LikeController::class, 'count']);

    // Routes protégées - authentification requise
    Route::middleware('auth:api')->group(function () {

        // Auth utilisateur connecté
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        // Gestion des infos utilisateur - préfixe 'me'
        Route::prefix('me')->group(function () {
            Route::get('/', [UserController::class, 'me']);
            Route::put('/', [UserController::class, 'update']);
            Route::delete('/', [UserController::class, 'destroy']);
            Route::post('change-password', [UserController::class, 'changePassword']);
            Route::post('avatar', [UserController::class, 'uploadAvatar']);
            Route::delete('avatar', [UserController::class, 'deleteAvatar']);
            Route::get('quota', [QuotaController::class, 'showQuota']);
            Route::post('parrainage', [ParrainageController::class, 'utiliserCodeParrain']);
            Route::get('actions-history', [UserController::class, 'getActionsHistory']);
            Route::get('my-resources', [UserController::class, 'getMyResources']);
            Route::get('my-downloads', [UserController::class, 'getMyDownloads']);
            Route::post('likes/toggle', [LikeController::class, 'toggle']);
        });

        // Gestion des messages (création, modification, suppression)
        Route::prefix('messages')->group(function () {
            Route::post('/', [MessageController::class, 'store']);
            Route::put('{id}', [MessageController::class, 'update']);
            Route::delete('{id}', [MessageController::class, 'destroy']);
        });

        // Gestion des ressources protégées
        Route::prefix('ressources')->group(function () {
            Route::post('/', [ResourceController::class, 'store']);
            Route::get('{id}/download', [ResourceController::class, 'download']);
            Route::delete('{id}', [ResourceController::class, 'destroy']);
        });

        // Gestion complète des métadonnées (CRUD)
        Route::apiResource('subjects', SubjectController::class)->except(['index', 'show']);
        Route::apiResource('series', SerieController::class)->except(['index', 'show']);
        Route::apiResource('classes', ClasseController::class)->except(['index', 'show']);
    });
});

