<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/categories', [CategoryController::class, 'create']);
Route::get('/categories', [CategoryController::class, 'getAll']);
Route::get('/categories/{category}', [CategoryController::class, 'get']);
Route::delete('/categories/{category}', [CategoryController::class, 'delete']);
Route::patch('/categories/{category}', [CategoryController::class, 'update']);



Route::get('/products', [ProductController::class, 'getAll']);
Route::get('/products/{product}', [ProductController::class, 'get']);
Route::post('/products', [ProductController::class, 'create']);