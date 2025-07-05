<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\CookbookController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\ClassificationController;
use App\Http\Controllers\Api\V1\SourceController;
use App\Http\Controllers\Api\V1\MealController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\PreparationController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for version 1 of your application.
|
*/

// Authentication Endpoints
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::post('password/email', [AuthController::class, 'sendPasswordResetEmail']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);
});

// User Endpoints
Route::group(['prefix' => 'users', 'middleware' => 'auth:sanctum'], function () {
    Route::get('profile', [UserController::class, 'getProfile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::get('subscription', [UserController::class, 'getSubscription']);
    Route::post('subscription', [UserController::class, 'updateSubscription']);
});

// Recipe Endpoints
Route::group(['prefix' => 'recipes'], function () {
    Route::get('public', [RecipeController::class, 'getPublicRecipes']);
    Route::get('search', [RecipeController::class, 'searchRecipes']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [RecipeController::class, 'index']);
        Route::post('/', [RecipeController::class, 'store']);
        Route::get('{id}', [RecipeController::class, 'show']);
        Route::put('{id}', [RecipeController::class, 'update']);
        Route::delete('{id}', [RecipeController::class, 'destroy']);
        Route::put('{id}/privacy', [RecipeController::class, 'togglePrivacy']);
    });
    
    // Print/Export Endpoints for Recipes
    Route::get('{id}/print', [RecipeController::class, 'getPrintableRecipe']);
    Route::get('{id}/export/{format}', [RecipeController::class, 'exportRecipe']);
});

// Cookbook Endpoints
Route::group(['prefix' => 'cookbooks'], function () {
    Route::get('public', [CookbookController::class, 'getPublicCookbooks']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [CookbookController::class, 'index']);
        Route::post('/', [CookbookController::class, 'store']);
        Route::get('{id}', [CookbookController::class, 'show']);
        Route::put('{id}', [CookbookController::class, 'update']);
        Route::delete('{id}', [CookbookController::class, 'destroy']);
        Route::put('{id}/privacy', [CookbookController::class, 'togglePrivacy']);
        Route::post('{id}/recipes', [CookbookController::class, 'addRecipes']);
        Route::delete('{id}/recipes/{recipe_id}', [CookbookController::class, 'removeRecipe']);
        Route::put('{id}/recipes/order', [CookbookController::class, 'reorderRecipes']);
    });
    
    // Print/Export Endpoints for Cookbooks
    Route::get('{id}/print', [CookbookController::class, 'getPrintableCookbook']);
    Route::get('{id}/export/{format}', [CookbookController::class, 'exportCookbook']);
});

// Admin Endpoints
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('users', [AdminController::class, 'listUsers']);
    Route::get('users/{id}', [AdminController::class, 'getUserDetails']);
    Route::put('users/{id}', [AdminController::class, 'updateUser']);
    Route::put('users/{id}/override', [AdminController::class, 'toggleAdminOverride']);
    Route::get('subscriptions', [AdminController::class, 'listSubscriptions']);
    Route::get('statistics', [AdminController::class, 'getSystemStatistics']);
});

// Metadata Endpoints
Route::get('classifications', [ClassificationController::class, 'index']);
Route::get('sources', [SourceController::class, 'index']);
Route::get('meals', [MealController::class, 'index']);
Route::get('courses', [CourseController::class, 'index']);
Route::get('preparations', [PreparationController::class, 'index']);
