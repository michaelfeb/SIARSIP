<?php

use App\Models\Role;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([1 => 'Mahasiswa', 2 => 'Resepsionis'] as $id => $nama) {
        $role = new Role(['nama' => $nama]);
        $role->id = $id;
        $role->save();
    }
});

test('legacy module URLs return gone for browser and mutation requests', function () {
    $this->get('/berkas-persuratan')->assertGone();
    $this->post('/berkas-persuratan/save')->assertGone();
    $this->get('/api/berkas-persuratan/legacy-document.pdf')->assertGone();
    $this->get('/jenis-surat')->assertGone();
});

test('legacy database tables are removed while the shared notes table remains', function () {
    expect(Schema::hasTable('berkas_persuratan'))->toBeFalse()
        ->and(Schema::hasTable('jenis_surat'))->toBeFalse()
        ->and(Schema::hasTable('notes'))->toBeTrue();
});

test('template surat is managed without the legacy letter-type relation', function () {
    Storage::fake('public');
    Storage::disk('public')->put('template_surat/cuti.docx', 'template');

    $template = TemplateSurat::query()->create([
        'nama' => 'Template Cuti Akademik',
        'deskripsi' => 'Dokumen contoh.',
        'status' => true,
        'dokumen_path' => 'template_surat/cuti.docx',
        'tanggal_publish' => now()->toDateString(),
    ]);

    $staff = User::factory()->create(['role_id' => 2]);

    $this->actingAs($staff)
        ->put(route('template-surat.save', ['id' => $template->id]), [
            'nama' => 'Template Cuti Akademik Diperbarui',
            'deskripsi' => 'Dokumen contoh terbaru.',
            'status' => true,
            'tanggal_publish' => now()->toDateString(),
        ])
        ->assertRedirect(route('template-surat.index'));

    $template->refresh();

    expect($template->nama)->toBe('Template Cuti Akademik Diperbarui')
        ->and($template->dokumen_path)->toBe('template_surat/cuti.docx')
        ->and(method_exists($template, 'jenisSurat'))->toBeFalse();

    $this->actingAs($staff)
        ->getJson(route('template-surat.index'))
        ->assertOk()
        ->assertJsonPath('data.0.id', $template->id);

    $this->get(route('template-surat.download', $template))
        ->assertOk();
});

test('students are redirected to sidang nol while non-students can access dashboard', function () {
    $student = User::factory()->create(['role_id' => 1]);
    $staff = User::factory()->create(['role_id' => 2]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertRedirect(route('berkas-sidang-nol.index'));

    $this->actingAs($staff)
        ->get(route('dashboard'))
        ->assertOk();
});

