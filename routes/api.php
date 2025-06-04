<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Ecole\ClasseController;
use App\Http\Controllers\Api\Ecole\SerieController;
use App\Http\Controllers\Api\Ecole\SubjectController;
use App\Http\Controllers\Api\Message\MessageController;
use App\Http\Controllers\Api\Other\LikeController;
use App\Http\Controllers\Api\Other\ParrainageController;
use App\Http\Controllers\Api\Other\QuotaController;
use App\Http\Controllers\Api\Profile\UserController;
use App\Http\Controllers\Api\Resource\ResourceController;
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

    // 🔓 Routes publiques

    // Authentification
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Ressources publiques
    Route::prefix('ressources')->group(function () {
        Route::get('/', [ResourceController::class, 'index']);
        Route::get('{id}', [ResourceController::class, 'show']);

        // Messages accessibles en lecture publique
        Route::get('{id}/messages', [MessageController::class, 'index']);
    });

    // Métadonnées publiques (matières, séries, classes)
    Route::apiResources([
        'subjects' => SubjectController::class,
        'series'   => SerieController::class,
        'classes'  => ClasseController::class,
    ], ['only' => ['index', 'show']]);

    // Compteur de likes (public)
    Route::get('likes/count/{resourceId}', [LikeController::class, 'count']);

    // 🔐 Routes protégées - authentification requise
    Route::middleware('auth:api')->group(function () {

        // Auth utilisateur connecté
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
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

