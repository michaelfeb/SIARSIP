<?php

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
    Route::resource('users', UserController::class);
});
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::match(['post', 'put'], '/users/save/{id?}', [UserController::class, 'save'])->name('users.save');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

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

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
