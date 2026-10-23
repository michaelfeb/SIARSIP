<?php

use App\Http\Controllers\BerkasSidangNolController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LinkLandingPageController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::any('/jenis-surat/{path?}', static fn () => abort(410, 'Modul Jenis Surat telah dihentikan.'))
    ->where('path', '.*')
    ->withoutMiddleware(ValidateCsrfToken::class);

Route::any('/berkas-persuratan/{path?}', static fn () => abort(410, 'Modul Berkas Persuratan telah dihentikan.'))
    ->where('path', '.*')
    ->withoutMiddleware(ValidateCsrfToken::class);

Route::any('/api/berkas-persuratan/{path?}', static fn () => abort(410, 'Modul Berkas Persuratan telah dihentikan.'))
    ->where('path', '.*')
    ->withoutMiddleware(ValidateCsrfToken::class);

Route::get('/template-surat/download/{id}', [TemplateSuratController::class, 'downloadTemplate'])->name('template-surat.download');

Route::middleware(['auth', 'role:2,3,4,5,6,7,8'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users-mahasiswa', [UserController::class, 'indexMahasiswa'])->name('users-mahasiswa.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::match(['post', 'put'], '/users/save/{id?}', [UserController::class, 'save'])->name('users.save');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/user/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('role:6,8');
});

Route::middleware(['auth'])->prefix('api')->name('api.users.')->group(function () {
    Route::get('/search-user', [UserController::class, 'search_user'])->name('search-user')->middleware('role:2,3,4,5,6,7,8,9');
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


Route::middleware('auth')->group(function () {
    Route::get('berkas-sidang-nol/download-upload', [BerkasSidangNolController::class, 'downloadUpload'])->name('berkas-sidang-nol.download-upload');

    Route::middleware(['auth'])->prefix('api')->name('api.berkas-sidang-nol.')->group(function () {
        Route::get('/berkas-sidang-nol/{path}', [BerkasSidangNolController::class, 'getDokumen'])
            ->where('path', '.*')
            ->name('getDokumen')
            ->middleware('role:6,8');
    });

    Route::get('/berkas-sidang-nol/export', [BerkasSidangNolController::class, 'export'])->name('berkas-sidang-nol.export')->middleware('role:6,8');

    Route::middleware('role:1,6,8')->group(function () {
        Route::get('berkas-sidang-nol', [BerkasSidangNolController::class, 'index'])->name('berkas-sidang-nol.index');
        Route::get('berkas-sidang-nol/create', [BerkasSidangNolController::class, 'create'])->name('berkas-sidang-nol.create');
        Route::match(['post', 'put'], 'berkas-sidang-nol/save', [BerkasSidangNolController::class, 'save'])->name('berkas-sidang-nol.saveCreate');
        Route::match(['post', 'put'], 'berkas-sidang-nol/save/{berkasSidangNol}', [BerkasSidangNolController::class, 'save'])->name('berkas-sidang-nol.saveEdit');
        Route::get('berkas-sidang-nol/{berkasSidangNol}/edit', [BerkasSidangNolController::class, 'edit'])->name('berkas-sidang-nol.edit');
        Route::put('berkas-sidang-nol/{berkasSidangNol}/kirim', [BerkasSidangNolController::class, 'kirim'])->name('berkas-sidang-nol.kirim');
        Route::get('berkas-sidang-nol/{berkasSidangNol}', [BerkasSidangNolController::class, 'show'])->name('berkas-sidang-nol.show');
        Route::delete('berkas-sidang-nol/{berkasSidangNol}', [BerkasSidangNolController::class, 'destroy'])->name('berkas-sidang-nol.destroy');
        Route::put('berkas-sidang-nol/{berkasSidangNol}/reset', [BerkasSidangNolController::class, 'reset'])->name('berkas-sidang-nol.reset');
        Route::get('berkas-sidang-nol/get-uploads/{berkasSidangNol}', [BerkasSidangNolController::class, 'getUploads'])->name('berkas-sidang-nol.get-uploads');
        Route::get('berkas-sidang-nol/{berkasSidangNol}/download-surat', [BerkasSidangNolController::class, 'downloadSurat'])->name('berkas-sidang-nol.download-surat-sidang-nol');
    });

    Route::middleware('role:6,8')->group(function () {
        Route::get('berkas-sidang-nol/{berkasSidangNol}/ajuan', [BerkasSidangNolController::class, 'ajuan'])->name('berkas-sidang-nol.ajuan');
        Route::post('berkas-sidang-nol/keputusan/{berkasSidangNol}', [BerkasSidangNolController::class, 'keputusan'])->name('berkas-sidang-nol.keputusan');
    });
});

Route::middleware(['auth', 'role:6,8'])->group(function () {
    Route::get('/carousel', [CarouselController::class, 'index'])->name('carousel.index');
    Route::get('/carousel/create', [CarouselController::class, 'create'])->name('carousel.create');
    Route::match(['post', 'put'], '/carousel/save/{id?}', [CarouselController::class, 'save'])->name('carousel.save');
    Route::get('/carousel/{id}/edit', [CarouselController::class, 'edit'])->name('carousel.edit');
    Route::put('/carousel/{id}/toggle', [CarouselController::class, 'toggle'])->name('carousel.toggle');
    Route::delete('/carousel/{id}', [CarouselController::class, 'destroy'])->name('carousel.destroy');
});

Route::get('/carousel-image/{filename}', [CarouselController::class, 'showImage'])->name('carousel.image');

Route::middleware(['auth', 'role:6,8'])->group(function () {
    Route::get('/link-landing-page', [LinkLandingPageController::class, 'index'])->name('link-landing-page.index');
    Route::get('/link-landing-page/create', [LinkLandingPageController::class, 'create'])->name('link-landing-page.create');
    Route::match(['post', 'put'], '/link-landing-page/save/{id?}', [LinkLandingPageController::class, 'save'])->name('link-landing-page.save');
    Route::get('/link-landing-page/{id}/edit', [LinkLandingPageController::class, 'edit'])->name('link-landing-page.edit');
    Route::put('/link-landing-page/{id}/toggle', [LinkLandingPageController::class, 'toggle'])->name('link-landing-page.toggle');
    Route::delete('/link-landing-page/{id}', [LinkLandingPageController::class, 'destroy'])->name('link-landing-page.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
