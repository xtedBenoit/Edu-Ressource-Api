<?php

use App\Http\Controllers\Api\Auth\AuthController;
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

// Routes publiques
Route::prefix('ressources')->group(function () {
    Route::get('/', [ResourceController::class, 'index']);   // Liste filtrée et paginée
    Route::get('{id}', [ResourceController::class, 'show']); // Voir une ressource
});

// Routes protégées (authentification requise)
Route::middleware('auth:api')->prefix('ressources')->group(function () {
    Route::post('/', [ResourceController::class, 'store']);        // Soumettre une ressource
    Route::get('{id}/download', [ResourceController::class, 'download']); // Télécharger
    Route::delete('{id}', [ResourceController::class, 'destroy']); // Supprimer
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});

Route::middleware('auth:api')->prefix('me')->group(function () {
    Route::get('/',       [UserController::class, 'me']);             // infos user connecté
    Route::put('/',       [UserController::class, 'update']);         // update profil
    Route::delete('/',    [UserController::class, 'destroy']);        // supprimer compte

    Route::post('change-password', [UserController::class, 'changePassword']); // changer mdp

    // avatar
    Route::post('avatar',   [UserController::class, 'uploadAvatar']);  // upload / modifier avatar
    Route::delete('avatar', [UserController::class, 'deleteAvatar']);  // supprimer avatar
});

