<?php

use Illuminate\Http\Request;
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

// API Versioning - V1
Route::prefix('v1')
    ->middleware(['api'])
    ->namespace('App\\Http\\Controllers\\Api\\V1')
    ->group(base_path('routes/api_v1.php'));
