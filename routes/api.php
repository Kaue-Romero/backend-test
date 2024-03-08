<?php

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RegisterLogController;
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

Route::group(['prefix' => 'register'], function () {
    Route::get('/', [RegisterController::class, 'index']);
    Route::post('/', [RegisterController::class, 'store']);
    Route::put('/{id}', [RegisterController::class, 'update']);
    Route::delete('/{id}', [RegisterController::class, 'destroy']);
    Route::post('/{id}/restore', [RegisterController::class, 'restore']);
});

Route::group(['prefix' => 'redirects'], function () {
    Route::get('/', [RegisterLogController::class, 'index']);
    Route::get('/{redirect}/stats', [RegisterLogController::class, 'stats']);
    Route::get('/{redirect}/log', [RegisterLogController::class, 'index']);
});
