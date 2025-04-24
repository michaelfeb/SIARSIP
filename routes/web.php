<?php

use App\Http\Controllers\BerkasPersuratanController;
use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Dashboard - dengan auth dan verified
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Landing Page Section
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/template-surat/download/{id}', [TemplateSuratController::class, 'downloadTemplate'])->name('template-surat.download');

// User Section
Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::match(['post', 'put'], '/users/save/{id?}', [UserController::class, 'save'])->name('users.save');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::prefix('api')->name('api.users.')->group(function () {
        Route::get('/search-user', [UserController::class, 'search_user'])->name('search-user');
    });
});

// Jenis Surat
Route::middleware(['auth'])->group(function () {
    Route::get('/jenis-surat', [JenisSuratController::class, 'index'])->name('jenis-surat.index');
    Route::get('/jenis-surat/create', [JenisSuratController::class, 'create'])->name('jenis-surat.create');
    Route::match(['post', 'put'], '/jenis-surat/save/{id?}', [JenisSuratController::class, 'save'])->name('jenis-surat.save');
    Route::get('/jenis-surat/{id}/edit', [JenisSuratController::class, 'edit'])->name('jenis-surat.edit');
    Route::put('/jenis-surat/{id}/toggle', [JenisSuratController::class, 'toggle'])->name('jenis-surat.toggle');
    Route::delete('/jenis-surat/{id}', [JenisSuratController::class, 'destroy'])->name('jenis-surat.destroy');
});

// Template Surat
Route::middleware(['auth'])->group(function () {
    Route::get('/template-surat', [TemplateSuratController::class, 'index'])->name('template-surat.index');
    Route::get('/template-surat/create', [TemplateSuratController::class, 'create'])->name('template-surat.create');
    Route::match(['post', 'put'], '/template-surat/save/{id?}', [TemplateSuratController::class, 'save'])->name('template-surat.save');
    Route::get('/template-surat/{id}', [TemplateSuratController::class, 'show'])->name('template-surat.show');
    Route::get('/template-surat/{id}/edit', [TemplateSuratController::class, 'edit'])->name('template-surat.edit');
    Route::delete('/template-surat/{id}', [TemplateSuratController::class, 'destroy'])->name('template-surat.destroy');
});

// Berkas Persuratan
Route::middleware(['auth'])->group(function () {
    Route::get('berkas-persuratan', [BerkasPersuratanController::class, 'index'])->name('berkas-persuratan.index');
    Route::get('berkas-persuratan/create', [BerkasPersuratanController::class, 'create'])->name('berkas-persuratan.create');
    Route::match(['post', 'put'], '/berkas-persuratan/save/{id?}', [BerkasPersuratanController::class, 'save'])->name('berkas-persuratan.save');
    Route::put('/berkas-persuratan/{id}/keputusan', [BerkasPersuratanController::class, 'keputusan'])->name('berkas-persuratan.keputusan');
    Route::get('berkas-persuratan/{id}', [BerkasPersuratanController::class, 'show'])->name('berkas-persuratan.show');
    Route::get('berkas-persuratan/{id}/ajuan', [BerkasPersuratanController::class, 'ajuan'])->name('berkas-persuratan.ajuan');
    Route::get('/berkas-persuratan/{id}/edit', [BerkasPersuratanController::class, 'edit'])->name('berkas-persuratan.edit');
    Route::delete('/berkas-persuratan/{id}', [BerkasPersuratanController::class, 'destroy'])->name('berkas-persuratan.destroy');
    Route::put('berkas-persuratan/{id}/kirim', [BerkasPersuratanController::class, 'kirim'])->name('berkas-persuratan.kirim');
    Route::put('berkas-persuratan/{id}/reset', [BerkasPersuratanController::class, 'reset'])->name('berkas-persuratan.reset');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
