<?php

use App\Http\Controllers\LevelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeranController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KegiatanDosenController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaDosenController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\NotifikasiController;
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
    Route::get('/kalender', [KalenderController::class, 'index']);
    Route::get('/mark-as-read/{id}', [NotifikasiController::class, 'markAsRead'])->name('notifications.markAsRead');

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

    Route::middleware(['authorize:ADM'])->group(function () {
        Route::group(['prefix' => 'periode'], function () {
            Route::get('/', [PeriodeController::class, 'index']);
            Route::post('/list', [PeriodeController::class, 'list']);
            Route::get('/create_ajax', [PeriodeController::class, 'create_ajax']);
            Route::post('/ajax', [PeriodeController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [PeriodeController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [PeriodeController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [PeriodeController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [PeriodeController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [PeriodeController::class, 'delete_ajax']);
            Route::get('/import', [PeriodeController::class, 'import']);
            Route::post('/import_ajax', [PeriodeController::class, 'import_ajax']);
            Route::get('/export_excel', [PeriodeController::class, 'export_excel']);
            Route::get('/export_pdf', [PeriodeController::class, 'export_pdf']);
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
    Route::middleware(['authorize:ADM,PMN,DSN'])->group(function () {
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

    Route::middleware(['authorize:DSN'])->group(function () {
        Route::group(['prefix' => 'kegiatan_dosen'], function () {
            Route::get('/', [KegiatanDosenController::class, 'index']);
            Route::post('/list', [KegiatanDosenController::class, 'list']);
            Route::get('/create_ajax', [KegiatanDosenController::class, 'create_ajax']);
            Route::post('/ajax', [KegiatanDosenController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [KegiatanDosenController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [KegiatanDosenController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [KegiatanDosenController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [KegiatanDosenController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [KegiatanDosenController::class, 'delete_ajax']);
            Route::get('/import', [KegiatanDosenController::class, 'import']);
            Route::post('/import_ajax', [KegiatanDosenController::class, 'import_ajax']);
            Route::get('/export_excel', [KegiatanDosenController::class, 'export_excel']);
            Route::get('/export_pdf', [KegiatanDosenController::class, 'export_pdf']);
            Route::get('/{id}/add_agenda', [AgendaController::class, 'add_agenda']);
            Route::get('/{id}/create_agenda_ajax', [AgendaController::class, 'create_ajax']);
            Route::post('/agenda_ajax', [AgendaController::class, 'store_ajax']);
            Route::get('/{id}/show_agenda_ajax', [AgendaController::class, 'show_ajax']);
            Route::get('/{id}/edit_agenda_ajax', [AgendaController::class, 'edit_ajax']);
            Route::put('/{id}/update_agenda_ajax', [AgendaController::class, 'update_ajax']);
            Route::get('/{id}/delete_agenda_ajax', [AgendaController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_agenda_ajax', [AgendaController::class, 'delete_ajax']);
        });
    });

    Route::middleware(['authorize:DSN'])->group(function () {
        Route::group(['prefix' => 'agenda_dosen'], function () {
            Route::get('/', [AgendaDosenController::class, 'index']);
            Route::post('/list', [AgendaDosenController::class, 'list']);
            Route::get('/create_ajax', [AgendaDosenController::class, 'create_ajax']);
            Route::post('/ajax', [AgendaDosenController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [AgendaDosenController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [AgendaDosenController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [AgendaDosenController::class, 'update_ajax']);
            Route::get('/{id}/delete_ajax', [AgendaDosenController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [AgendaDosenController::class, 'delete_ajax']);
            Route::get('/import', [AgendaDosenController::class, 'import']);
            Route::post('/import_ajax', [AgendaDosenController::class, 'import_ajax']);
            Route::get('/export_excel', [AgendaDosenController::class, 'export_excel']);
            Route::get('/export_pdf', [AgendaDosenController::class, 'export_pdf']);
        });
    });

    Route::middleware(['authorize:ADM,PMN'])->group(function () {
        Route::group(['prefix' => 'statistik'], function () {
            Route::get('/', [StatistikController::class, 'index']);
            Route::post('/list', [StatistikController::class, 'list']);
            Route::get('/create_ajax', [StatistikController::class, 'create_ajax']);
            Route::post('/ajax', [StatistikController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [StatistikController::class, 'show_ajax']);
            Route::get('/export_excel', [StatistikController::class, 'export_excel']);
            Route::get('/export_pdf', [StatistikController::class, 'export_pdf']);
            Route::get('/{id}/export_statistik', [StatistikController::class, 'export_statistik']);
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
