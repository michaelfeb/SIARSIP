<?php

use App\Http\Controllers\BerkasSidangNolController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\CutiAkademikController;
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

Route::middleware(['auth', 'role:1,2,3,5,6,8,12'])->prefix('cuti-akademik')->name('cuti-akademik.')->group(function () {
    Route::get('/', [CutiAkademikController::class, 'index'])->name('index');
    Route::get('/create', [CutiAkademikController::class, 'create'])->name('create')->middleware('role:1,8');
    Route::post('/', [CutiAkademikController::class, 'store'])->name('store')->middleware('role:1,8');
    Route::get('/{letter}', [CutiAkademikController::class, 'show'])->name('show');
    Route::get('/{letter}/edit', [CutiAkademikController::class, 'edit'])->name('edit')->middleware('role:1,8');
    Route::put('/{letter}', [CutiAkademikController::class, 'update'])->name('update')->middleware('role:1,8');
    Route::delete('/{letter}', [CutiAkademikController::class, 'destroy'])->name('destroy')->middleware('role:1,8');
    Route::put('/{letter}/submit', [CutiAkademikController::class, 'submit'])->name('submit')->middleware('role:1,8');
    Route::get('/{letter}/ajuan', [CutiAkademikController::class, 'review'])->name('review');
    Route::post('/{letter}/decision', [CutiAkademikController::class, 'decide'])->name('decide');
    Route::post('/{letter}/finalise', [CutiAkademikController::class, 'finalize'])->name('finalize');
    Route::get('/{letter}/attachments/{attachment}', [CutiAkademikController::class, 'showAttachment'])->name('show-attachment');
    Route::get('/{letter}/download', [CutiAkademikController::class, 'downloadFinalLetter'])->name('download-final-letter');
});

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
    Route::get('berkas-sidang-nol/download-upload', [BerkasSidangNolController::class, 'showAttachment'])->name('berkas-sidang-nol.show-attachment');

    Route::middleware(['auth'])->prefix('api')->name('api.berkas-sidang-nol.')->group(function () {
        Route::get('/berkas-sidang-nol/{path}', [BerkasSidangNolController::class, 'showDocument'])
            ->where('path', '.*')
            ->name('show-document')
            ->middleware('role:6,8');
    });

    Route::get('/berkas-sidang-nol/export', [BerkasSidangNolController::class, 'export'])->name('berkas-sidang-nol.export')->middleware('role:6,8');

    Route::middleware('role:1,6,8')->group(function () {
        Route::get('berkas-sidang-nol', [BerkasSidangNolController::class, 'index'])->name('berkas-sidang-nol.index');
        Route::get('berkas-sidang-nol/create', [BerkasSidangNolController::class, 'create'])->name('berkas-sidang-nol.create');
        Route::post('berkas-sidang-nol/save', [BerkasSidangNolController::class, 'store'])->name('berkas-sidang-nol.store');
        Route::post('berkas-sidang-nol/save/{berkasSidangNol}', [BerkasSidangNolController::class, 'update'])->name('berkas-sidang-nol.update');
        Route::get('berkas-sidang-nol/{berkasSidangNol}/edit', [BerkasSidangNolController::class, 'edit'])->name('berkas-sidang-nol.edit');
        Route::put('berkas-sidang-nol/{berkasSidangNol}/kirim', [BerkasSidangNolController::class, 'submit'])->name('berkas-sidang-nol.submit');
        Route::get('berkas-sidang-nol/{berkasSidangNol}', [BerkasSidangNolController::class, 'show'])->name('berkas-sidang-nol.show');
        Route::delete('berkas-sidang-nol/{berkasSidangNol}', [BerkasSidangNolController::class, 'destroy'])->name('berkas-sidang-nol.destroy');
        Route::put('berkas-sidang-nol/{berkasSidangNol}/reset', [BerkasSidangNolController::class, 'resetToDraft'])->name('berkas-sidang-nol.reset-to-draft');
        Route::get('berkas-sidang-nol/get-uploads/{berkasSidangNol}', [BerkasSidangNolController::class, 'attachments'])->name('berkas-sidang-nol.attachments');
        Route::get('berkas-sidang-nol/{berkasSidangNol}/download-surat', [BerkasSidangNolController::class, 'downloadFinalLetter'])->name('berkas-sidang-nol.download-final-letter');
    });

    Route::middleware('role:6,8')->group(function () {
        Route::get('berkas-sidang-nol/{berkasSidangNol}/ajuan', [BerkasSidangNolController::class, 'review'])->name('berkas-sidang-nol.review');
        Route::post('berkas-sidang-nol/keputusan/{berkasSidangNol}', [BerkasSidangNolController::class, 'decide'])->name('berkas-sidang-nol.decide');
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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
