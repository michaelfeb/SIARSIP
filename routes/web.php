<?php

use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', function () {
    return Inertia::render('Dashboard/index');
})->middleware(['auth', 'verified'])->name('home');

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

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
