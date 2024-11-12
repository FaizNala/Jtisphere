<?php

use App\Http\Controllers\LevelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeranController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\ProfileController;
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



// Pattern enforcement for 'id' parameter (must be a number)
Route::pattern('id', '[0-9]+');
Route::get('/home', [HomeController::class, 'index'])->name('home');
// Auth routes
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'postlogin']);

Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'postRegister']);

Route::get('logout', [AuthController::class, 'logout'])->middleware('auth');

// Routes that require authentication
Route::middleware(['auth'])->group(function () {

    // Welcome route
    Route::get('/', [WelcomeController::class, 'index']);
    Route::get('/switch-role/{level_id}', [UserController::class, 'switchRole'])->name('switch.role');

    // Semua route di dalam group ini harus punya role ADM
    Route::middleware(['authorize:ADM'])->group(function () {
        Route::group(['prefix' => 'level'], function () {
            Route::get('/', [LevelController::class, 'index']);
            Route::post('/list', [LevelController::class, 'list']);
            Route::get('/create_ajax', [LevelController::class, 'create_ajax']);
            Route::post('/ajax', [LevelController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [LevelController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [LevelController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [LevelController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [LevelController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [LevelController::class, 'delete_ajax']);
            Route::get('/import', [LevelController::class, 'import']);
            Route::post('/import_ajax', [LevelController::class, 'import_ajax']);
            Route::get('/export_excel', [LevelController::class, 'export_excel']);
            Route::get('/export_pdf', [LevelController::class, 'export_pdf']);
        });
    });

    // User routes
    Route::middleware(['authorize:ADM,PMN'])->group(function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/list', [UserController::class, 'list']);
            Route::get('/create_ajax', [UserController::class, 'create_ajax']);
            Route::post('/ajax', [UserController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [UserController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [UserController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [UserController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [UserController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [UserController::class, 'delete_ajax']);
            Route::get('/import', [UserController::class, 'import']);
            Route::post('/import_ajax', [UserController::class, 'import_ajax']);
            Route::get('/export_excel', [UserController::class, 'export_excel']);
            Route::get('/export_pdf', [UserController::class, 'export_pdf']);
        });
    });

    // Kategori routes
    Route::middleware(['authorize:ADM'])->group(function () {
        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KategoriController::class, 'index']);
            Route::post('/list', [KategoriController::class, 'list']);
            Route::get('/create_ajax', [KategoriController::class, 'create_ajax']);
            Route::post('/ajax', [KategoriController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [KategoriController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [KategoriController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [KategoriController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [KategoriController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [KategoriController::class, 'delete_ajax']);
            Route::get('/import', [KategoriController::class, 'import']);
            Route::post('/import_ajax', [KategoriController::class, 'import_ajax']);
            Route::get('/export_excel', [KategoriController::class, 'export_excel']);
            Route::get('/export_pdf', [KategoriController::class, 'export_pdf']);
        });
    });

    // Peran routes
    Route::middleware(['authorize:ADM'])->group(function () {
        Route::group(['prefix' => 'peran'], function () {
            Route::get('/', [PeranController::class, 'index']);
            Route::post('/list', [PeranController::class, 'list']);
            Route::get('/create_ajax', [PeranController::class, 'create_ajax']);
            Route::post('/ajax', [PeranController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [PeranController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [PeranController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [PeranController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [PeranController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [PeranController::class, 'delete_ajax']);
            Route::get('/import', [PeranController::class, 'import']);
            Route::post('/import_ajax', [PeranController::class, 'import_ajax']);
            Route::get('/export_excel', [PeranController::class, 'export_excel']);
            Route::get('/export_pdf', [PeranController::class, 'export_pdf']);
        });
    });

    // Kegiatan routes
    Route::middleware(['authorize:ADM,PMN'])->group(function () {
        Route::group(['prefix' => 'kegiatan'], function () {
            Route::get('/', [KegiatanController::class, 'index']);
            Route::post('/list', [KegiatanController::class, 'list']);
            Route::get('/create_ajax', [KegiatanController::class, 'create_ajax']);
            Route::post('/ajax', [KegiatanController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [KegiatanController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [KegiatanController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [KegiatanController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [KegiatanController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [KegiatanController::class, 'delete_ajax']);
            Route::get('/import', [KegiatanController::class, 'import']);
            Route::post('/import_ajax', [KegiatanController::class, 'import_ajax']);
            Route::get('/export_excel', [KegiatanController::class, 'export_excel']);
            Route::get('/export_pdf', [KegiatanController::class, 'export_pdf']);
            Route::get('/{id}/export_draft_surat_tugas', [KegiatanController::class, 'export_draft_surat_tugas']);
        });
    });

    Route::middleware('authorize:ADM,MNG,STF')->group(function () {
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProfileController::class, 'index']);
            Route::get('/edit_ajax', [ProfileController::class, 'edit_ajax']);
            Route::post('/update_ajax', [ProfileController::class, 'update_ajax']);
        });
    });
});
