<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// API routes are handled separately in routes/api.php and routes/api_v1.php

// Catch-all route for Vue.js SPA
// This should handle all frontend routes and let Vue Router take over
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
