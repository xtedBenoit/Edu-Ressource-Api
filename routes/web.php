<?php

use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/browse_resources/', [ResourceController::class, 'show']);