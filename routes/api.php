<?php

use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UserController;
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

Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'user'], function () {
    Route::get('/get_user', [UserController::class, 'get_user']);
    Route::get('/get_dosen', [UserController::class, 'get_dosen']);
});

Route::group(['prefix' => 'kegiatan'], function () {
    Route::get('/get_kegiatan', [KegiatanController::class, 'get_kegiatan']);
    Route::get('/get_kegiatan_dosen/{id}', [KegiatanController::class, 'get_kegiatan_dosen']);
    Route::get('/get_kegiatan_detail/{id}', [KegiatanController::class, 'get_kegiatan_detail']);
    Route::get('/get_kegiatan_detail2/{id}', [KegiatanController::class, 'get_kegiatan_detail2']);
});

Route::group(['prefix' => 'profile', 'middleware' => 'auth:api'], function () {
    Route::get('/', [App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::delete('/', [App\Http\Controllers\Api\ProfileController::class, 'delete']);
});
