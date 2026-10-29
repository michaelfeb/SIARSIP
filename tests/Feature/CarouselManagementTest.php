<?php

use App\Models\Carousel;
use App\Models\Role;
use App\Models\User;
use App\Services\CarouselService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([2 => 'Resepsionis', 6 => 'Akademik', 8 => 'Super Admin'] as $id => $nama) {
        $role = new Role(['nama' => $nama]);
        $role->id = $id;
        $role->save();
    }

    Storage::fake('public');
});

test('management index filters carousel records through validated query input', function () {
    $actor = User::factory()->create(['role_id' => 6]);
    $matching = Carousel::create(['nama' => 'Pengumuman Akademik', 'gambar' => 'carousel/akademik.jpg', 'status' => true, 'tanggal_publish' => '2026-10-29']);
    Carousel::create(['nama' => 'Kegiatan Mahasiswa', 'gambar' => 'carousel/kegiatan.jpg', 'status' => true, 'tanggal_publish' => '2026-10-28']);

    $this->actingAs($actor)
        ->getJson(route('carousel.index', ['search' => 'Akademik']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id);

    $this->actingAs($actor)
        ->getJson(route('carousel.index', ['per_page' => 101]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
});

test('authorized staff can create, retain, replace, toggle, and delete a carousel image', function () {
    $actor = User::factory()->create(['role_id' => 6]);

    $this->actingAs($actor)
        ->post(route('carousel.store'), [
            'nama' => 'Pengumuman Baru',
            'gambar' => UploadedFile::fake()->image('awal.jpg'),
            'status' => true,
            'tanggal_publish' => '2026-10-29',
        ])
        ->assertRedirect(route('carousel.index'));

    $carousel = Carousel::firstOrFail();
    $firstImagePath = $carousel->gambar;
    Storage::disk('public')->assertExists($firstImagePath);

    $this->actingAs($actor)
        ->put(route('carousel.update', $carousel), [
            'nama' => 'Pengumuman Diperbarui',
            'status' => false,
            'tanggal_publish' => '2026-10-29',
        ])
        ->assertRedirect(route('carousel.index'));

    expect($carousel->fresh()->gambar)->toBe($firstImagePath);
    Storage::disk('public')->assertExists($firstImagePath);

    $this->actingAs($actor)
        ->put(route('carousel.update', $carousel), [
            'nama' => 'Pengumuman Diperbarui',
            'gambar' => UploadedFile::fake()->image('pengganti.png'),
            'status' => false,
            'tanggal_publish' => '2026-10-29',
        ])
        ->assertRedirect(route('carousel.index'));

    $carousel->refresh();
    expect($carousel->gambar)->not->toBe($firstImagePath);
    Storage::disk('public')->assertMissing($firstImagePath);
    Storage::disk('public')->assertExists($carousel->gambar);

    $this->actingAs($actor)->put(route('carousel.toggle', $carousel))->assertRedirect(route('carousel.index'));
    expect($carousel->fresh()->status)->toBeTrue();

    $latestImagePath = $carousel->gambar;
    $this->actingAs($actor)->delete(route('carousel.destroy', $carousel))->assertRedirect(route('carousel.index'));
    $this->assertDatabaseMissing('carousels', ['id' => $carousel->id]);
    Storage::disk('public')->assertMissing($latestImagePath);
});

test('carousel service rejects actors outside the management roles and landing data contains only active records', function () {
    $unauthorizedActor = User::factory()->create(['role_id' => 2]);
    $active = Carousel::create(['nama' => 'Aktif Baru', 'gambar' => 'carousel/aktif-baru.jpg', 'status' => true, 'tanggal_publish' => '2026-10-29']);
    Carousel::create(['nama' => 'Tidak Aktif', 'gambar' => 'carousel/tidak-aktif.jpg', 'status' => false, 'tanggal_publish' => '2026-10-30']);
    Carousel::create(['nama' => 'Aktif Lama', 'gambar' => 'carousel/aktif-lama.jpg', 'status' => true, 'tanggal_publish' => '2026-10-28']);

    expect(fn () => app(CarouselService::class)->toggleStatus($active, $unauthorizedActor))
        ->toThrow(AuthorizationException::class);

    expect(app(CarouselService::class)->publishedForLanding()->pluck('id')->all())
        ->toBe([$active->id, Carousel::where('nama', 'Aktif Lama')->value('id')]);

    $this->get(route('carousel.image', 'missing.jpg'))->assertNotFound();
});
