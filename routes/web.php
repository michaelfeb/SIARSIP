<?php

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

// User Section //
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

// routes/web.php
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::match(['post', 'put'], '/users/save/{id?}', [UserController::class, 'save'])->name('users.save');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
