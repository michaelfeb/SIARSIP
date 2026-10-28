<?php

use App\Models\Role;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([1 => 'Mahasiswa', 2 => 'Resepsionis'] as $id => $nama) {
        $role = new Role(['nama' => $nama]);
        $role->id = $id;
        $role->save();
    }
    Storage::fake('public');
});

test('student JSON listing contains only published templates', function () {
    $student = User::factory()->create(['role_id' => 1]);
    $active = TemplateSurat::create(['nama' => 'Aktif', 'status' => true, 'dokumen_path' => 'template_surat/aktif.docx', 'tanggal_publish' => now()->toDateString()]);
    TemplateSurat::create(['nama' => 'Tidak Aktif', 'status' => false, 'dokumen_path' => 'template_surat/nonaktif.docx', 'tanggal_publish' => now()->toDateString()]);

    $this->actingAs($student)->getJson(route('template-surat.mahasiswa'))
        ->assertOk()->assertJsonPath('data.0.id', $active->id)->assertJsonCount(1, 'data');
});

test('staff can create and replace a template document while old storage is cleaned up', function () {
    $staff = User::factory()->create(['role_id' => 2]);
    $this->actingAs($staff)->post(route('template-surat.store'), [
        'nama' => 'Template Baru', 'deskripsi' => 'Dokumen', 'status' => true,
        'tanggal_publish' => '2026-10-27', 'dokumen_path' => UploadedFile::fake()->create('awal.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    ])->assertRedirect(route('template-surat.index'));

    $template = TemplateSurat::firstOrFail();
    $oldPath = $template->dokumen_path;
    Storage::disk('public')->assertExists($oldPath);

    $this->actingAs($staff)->put(route('template-surat.update', $template), [
        'nama' => 'Template Diperbarui', 'deskripsi' => 'Dokumen baru', 'status' => false,
        'tanggal_publish' => '2026-10-28', 'dokumen_path' => UploadedFile::fake()->create('baru.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    ])->assertRedirect(route('template-surat.index'));

    $template->refresh();
    expect($template->nama)->toBe('Template Diperbarui')->and($template->dokumen_path)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($template->dokumen_path);
});

test('editing without a new document retains the current stored document', function () {
    $staff = User::factory()->create(['role_id' => 2]);
    Storage::disk('public')->put('template_surat/lama.docx', 'old');
    $template = TemplateSurat::create(['nama' => 'Template', 'status' => true, 'dokumen_path' => 'template_surat/lama.docx', 'tanggal_publish' => now()->toDateString()]);

    $this->actingAs($staff)->put(route('template-surat.update', $template), ['nama' => 'Template', 'status' => true, 'tanggal_publish' => now()->toDateString()])
        ->assertRedirect(route('template-surat.index'));

    expect($template->fresh()->dokumen_path)->toBe('template_surat/lama.docx');
    Storage::disk('public')->assertExists('template_surat/lama.docx');
});
