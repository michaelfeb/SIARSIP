<?php

use App\Http\Controllers\BerkasPersuratanController;
use App\Http\Controllers\BerkasSidangNolController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'role:2,3,4,5,6,7,8,9']);

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::get('/template-surat/download/{id}', [TemplateSuratController::class, 'downloadTemplate'])->name('template-surat.download');

Route::middleware(['auth', 'role:2,3,4,5,6,7,8'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::match(['post', 'put'], '/users/save/{id?}', [UserController::class, 'save'])->name('users.save');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth'])->prefix('api')->name('api.users.')->group(function () {
    Route::get('/search-user', [UserController::class, 'search_user'])->name('search-user')->middleware('role:2,3,4,5,6,7,8,9');
});

Route::middleware(['auth', 'role:2,3,4,5,6,7,8'])->group(function () {
    Route::get('/jenis-surat', [JenisSuratController::class, 'index'])->name('jenis-surat.index');
    Route::get('/jenis-surat/create', [JenisSuratController::class, 'create'])->name('jenis-surat.create');
    Route::match(['post', 'put'], '/jenis-surat/save/{id?}', [JenisSuratController::class, 'save'])->name('jenis-surat.save');
    Route::get('/jenis-surat/{id}/edit', [JenisSuratController::class, 'edit'])->name('jenis-surat.edit');
    Route::put('/jenis-surat/{id}/toggle', [JenisSuratController::class, 'toggle'])->name('jenis-surat.toggle');
    Route::delete('/jenis-surat/{id}', [JenisSuratController::class, 'destroy'])->name('jenis-surat.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/template-surat', [TemplateSuratController::class, 'index'])->name('template-surat.index');
    Route::get('/template-surat-mahasiswa', [TemplateSuratController::class, 'indexMahasiswa'])->name('template-surat.mahasiswa');
    Route::get('/template-surat/create', [TemplateSuratController::class, 'create'])->name('template-surat.create')->middleware('role:2,3,4,5,6,7,8');
    Route::match(['post', 'put'], '/template-surat/save/{id?}', [TemplateSuratController::class, 'save'])->name('template-surat.save')->middleware('role:2,3,4,5,6,7,8');
    Route::get('/template-surat/{id}', [TemplateSuratController::class, 'show'])->name('template-surat.show');
    Route::get('/template-surat/{id}/edit', [TemplateSuratController::class, 'edit'])->name('template-surat.edit')->middleware('role:2,3,4,5,6,7,8');
    Route::delete('/template-surat/{id}', [TemplateSuratController::class, 'destroy'])->name('template-surat.destroy')->middleware('role:2,3,4,5,6,7,8');
});

Route::middleware(['auth'])->group(function () {
    Route::get('berkas-persuratan', [BerkasPersuratanController::class, 'index'])->name('berkas-persuratan.index');
    Route::get('berkas-persuratan/create', [BerkasPersuratanController::class, 'create'])->name('berkas-persuratan.create');
    Route::match(['post', 'put'], '/berkas-persuratan/save/{id?}', [BerkasPersuratanController::class, 'save'])->name('berkas-persuratan.save');
    Route::post('/berkas-persuratan/{id}/keputusan', [BerkasPersuratanController::class, 'keputusan'])->name('berkas-persuratan.keputusan')->middleware('role:2,3,4,5,6,7,8');
    Route::get('berkas-persuratan/{id}', [BerkasPersuratanController::class, 'show'])->name('berkas-persuratan.show');
    Route::get('berkas-persuratan/{id}/ajuan', [BerkasPersuratanController::class, 'ajuan'])->name('berkas-persuratan.ajuan')->middleware('role:2,3,4,5,6,7,8');
    Route::get('/berkas-persuratan/{id}/edit', [BerkasPersuratanController::class, 'edit'])->name('berkas-persuratan.edit');
    Route::post('/berkas-persuratan/{id}/kirim', [BerkasPersuratanController::class, 'kirim'])->name('berkas-persuratan.kirim-persuratan');
    Route::delete('/berkas-persuratan/{id}', [BerkasPersuratanController::class, 'destroy'])->name('berkas-persuratan.destroy');
    Route::put('berkas-persuratan/{id}/reset', [BerkasPersuratanController::class, 'reset'])->name('berkas-persuratan.reset');
    Route::get('/berkas-persuratan/{id}/download-balasan', [BerkasPersuratanController::class, 'downloadBalasan'])->name('berkas-persuratan.download-balasan');

    Route::get('berkas-persuratan/download-upload/{filename}', [BerkasPersuratanController::class, 'downloadUpload'])
        ->where('filename', '.*')
        ->name('berkas-persuratan.download-upload');
});


Route::middleware('auth')->group(function () {
    Route::get('berkas-sidang-nol/download-upload', [BerkasSidangNolController::class, 'downloadUpload'])->name('berkas-sidang-nol.download-upload');

    Route::middleware('role:1,6,8')->group(function () {
        Route::get('berkas-sidang-nol', [BerkasSidangNolController::class, 'index'])->name('berkas-sidang-nol.index');
        Route::get('berkas-sidang-nol/create', [BerkasSidangNolController::class, 'create'])->name('berkas-sidang-nol.create');
        Route::match(['post', 'put'], 'berkas-sidang-nol/save/{id?}', [BerkasSidangNolController::class, 'save'])->name('berkas-sidang-nol.save');
        Route::get('berkas-sidang-nol/{id}/edit', [BerkasSidangNolController::class, 'edit'])->name('berkas-sidang-nol.edit');
        Route::put('berkas-sidang-nol/{id}/kirim', [BerkasSidangNolController::class, 'kirim'])->name('berkas-sidang-nol.kirim');
        Route::get('berkas-sidang-nol/{id}', [BerkasSidangNolController::class, 'show'])->name('berkas-sidang-nol.show');
        Route::delete('berkas-sidang-nol/{id}', [BerkasSidangNolController::class, 'destroy'])->name('berkas-sidang-nol.destroy');
        Route::put('berkas-sidang-nol/{id}/reset', [BerkasSidangNolController::class, 'reset'])->name('berkas-sidang-nol.reset');
        Route::get('berkas-sidang-nol/get-uploads/{id}', [BerkasSidangNolController::class, 'getUploads'])->name('berkas-sidang-nol.get-uploads');
        Route::get('berkas-sidang-nol/{id}/download-surat', [BerkasSidangNolController::class, 'downloadSurat'])->name('berkas-sidang-nol.download-surat-sidang-nol');
    });

    Route::middleware('role:6,8')->group(function () {
        Route::get('berkas-sidang-nol/{id}/ajuan', [BerkasSidangNolController::class, 'ajuan'])->name('berkas-sidang-nol.ajuan');
        Route::post('berkas-sidang-nol/keputusan/{id}', [BerkasSidangNolController::class, 'keputusan'])->name('berkas-sidang-nol.keputusan');
    });
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
