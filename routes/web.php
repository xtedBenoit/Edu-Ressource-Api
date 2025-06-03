<?php

use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
