<?php

use App\Models\Letter;
use App\Models\User;
use App\Services\CutiAkademikService;
use App\Services\LetterQrCodeService;
use Database\Seeders\CutiAkademikLetterTypeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CutiAkademikLetterTypeSeeder::class);
    Storage::fake('secure');
});

function cutiUser(int $roleId, ?int $programStudi = 6): User
{
    return User::factory()->create([
        'role_id' => $roleId,
        'nim' => fake()->unique()->numerify('24############'),
        'program_studi' => $programStudi,
        'nomor_telpon' => '081234567890',
    ]);
}

/** @return array<string, mixed> */
function cutiPayload(array $overrides = []): array
{
    return [
        'semester' => '5',
        'tahun_akademik' => '2026/2027',
        'nomor_hp' => '081234567890',
        'nominal_ukt' => '2500000',
        'alasan_cuti' => 'Keperluan keluarga.',
        'attachments' => [UploadedFile::fake()->create('surat-pendukung.pdf', 50, 'application/pdf')],
        ...$overrides,
    ];
}

test('a finalized letter QR code is generated as a PNG image', function () {
    $letter = new Letter;
    $letter->setRawAttributes([
        'id' => 123,
        'letter_number' => '123/UN8.1/CA/2026',
    ]);

    $qrCode = app(LetterQrCodeService::class)->forLetter($letter);
    $image = base64_decode(explode(',', $qrCode, 2)[1], true);

    expect($qrCode)->toStartWith('data:image/png;base64,')
        ->and($image)->toStartWith("\x89PNG\r\n\x1a\n");
});

test('a mahasiswa creates a Cuti Akademik draft with a biodata snapshot and private attachment', function () {
    $student = cutiUser(1);
    $letter = app(CutiAkademikService::class)->create($student, cutiPayload());

    expect($letter->status)->toBe('DRAFT')
        ->and($letter->applicant_name)->toBe($student->nama)
        ->and($letter->applicant_id_number)->toBe($student->nim)
        ->and($letter->program_studi)->toBe('Ilmu Komputer')
        ->and($letter->meta)->toMatchArray([
            'semester' => '5',
            'tahun_akademik' => '2026/2027',
            'program_studi_id' => 6,
        ])
        ->and($letter->attachments)->toHaveCount(1);

    Storage::disk('secure')->assertExists($letter->attachments->first()->file_path);
});

test('the workflow only moves through the approved immediate stages', function () {
    $student = cutiUser(1);
    $prodi = cutiUser(12, 6);
    $akademik = cutiUser(6);
    $ktu = cutiUser(5);
    $dekan = cutiUser(3);
    $service = app(CutiAkademikService::class);
    $letter = $service->create($student, cutiPayload());

    $service->ajuan($letter, $student);
    expect($letter->fresh()->current_stage)->toBe('PRODI');

    expect(fn () => $service->decide($letter->fresh(), $akademik, 'FORWARD', null))->toThrow(AuthorizationException::class);

    $service->decide($letter->fresh(), $prodi, 'FORWARD', 'Lengkap');
    expect($letter->fresh()->current_stage)->toBe('AKADEMIK');
    $service->decide($letter->fresh(), $akademik, 'FORWARD', null);
    expect($letter->fresh()->current_stage)->toBe('KTU');
    $service->decide($letter->fresh(), $ktu, 'FORWARD', null);
    expect($letter->fresh()->current_stage)->toBe('DEKAN');
    $service->decide($letter->fresh(), $dekan, 'FORWARD', null);

    expect($letter->fresh()->current_stage)->toBe('RESEPSIONIS')
        ->and($letter->routes()->count())->toBe(5);
});

test('the detail workflow context uses the configured Letter Type approval chain', function () {
    $student = cutiUser(1);
    $service = app(CutiAkademikService::class);
    $letter = $service->create($student, cutiPayload());

    expect($service->workflowDisplayContext($letter))->toBe([
        'approvalChain' => ['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS'],
    ]);
});

test('resepsionis finalises after DEKAN with a private generated PDF', function () {
    $student = cutiUser(1);
    $prodi = cutiUser(12, 6);
    $akademik = cutiUser(6);
    $ktu = cutiUser(5);
    $dekan = cutiUser(3);
    $resepsionis = cutiUser(2);
    $service = app(CutiAkademikService::class);
    $letter = $service->create($student, cutiPayload());

    $service->ajuan($letter, $student);
    $service->decide($letter->fresh(), $prodi, 'FORWARD', null);
    $service->decide($letter->fresh(), $akademik, 'FORWARD', null);
    $service->decide($letter->fresh(), $ktu, 'FORWARD', null);
    $service->decide($letter->fresh(), $dekan, 'FORWARD', null);

    expect($service->availableFinalSignatories($resepsionis, $letter->fresh()))
        ->toContain(['id' => $dekan->id, 'name' => $dekan->nama]);

    $service->finalize($letter->fresh(), $resepsionis, [
        'letter_number' => '123/UN8.1/CA/2026',
        'signatory_id' => $dekan->id,
        'note' => 'Selesai.',
    ]);

    $finished = $letter->fresh();
    expect($finished->status)->toBe('ARCHIVED')
        ->and($finished->current_stage)->toBeNull()
        ->and($finished->letter_number)->toBe('123/UN8.1/CA/2026')
        ->and($finished->final_pdf_path)->not->toBeNull()
        ->and($finished->meta['signatory']['id'])->toBe($dekan->id)
        ->and($finished->meta['signatory']['nim'])->toBe($dekan->nim);

    Storage::disk('secure')->assertExists($finished->final_pdf_path);
    expect(Storage::disk('secure')->get($finished->final_pdf_path))->toStartWith('%PDF');
});

test('finalising a letter redirects the user to the Cuti Akademik index', function () {
    $student = cutiUser(1);
    $prodi = cutiUser(12, 6);
    $akademik = cutiUser(6);
    $ktu = cutiUser(5);
    $dekan = cutiUser(3);
    $resepsionis = cutiUser(2);
    $service = app(CutiAkademikService::class);
    $letter = $service->create($student, cutiPayload());

    $service->ajuan($letter, $student);
    $service->decide($letter->fresh(), $prodi, 'FORWARD', null);
    $service->decide($letter->fresh(), $akademik, 'FORWARD', null);
    $service->decide($letter->fresh(), $ktu, 'FORWARD', null);
    $service->decide($letter->fresh(), $dekan, 'FORWARD', null);

    $this->actingAs($resepsionis)
        ->post(route('cuti-akademik.finalize', $letter), [
            'decision' => 'FORWARD',
            'letter_number' => '125/UN8.1/CA/2026',
            'signatory_id' => $dekan->id,
        ])
        ->assertRedirect(route('cuti-akademik.index'))
        ->assertSessionHas('success', 'Surat Cuti Akademik berhasil dibuat dan diarsipkan.');
});

test('super admin can administer every Cuti Akademik workflow stage but cannot become the signer', function () {
    $student = cutiUser(1);
    $superAdmin = cutiUser(8);
    $dekan = cutiUser(3);
    $service = app(CutiAkademikService::class);

    $adminDraft = $service->create($superAdmin, cutiPayload());
    expect($adminDraft->status)->toBe('DRAFT');

    $letter = $service->create($student, cutiPayload());
    $service->update($letter, $superAdmin, cutiPayload([
        'semester' => '6',
        'attachments' => [],
    ]));
    expect($letter->fresh()->meta)->toMatchArray([
        'semester' => '6',
        'program_studi_id' => $student->program_studi,
    ]);

    $service->ajuan($letter->fresh(), $superAdmin);
    foreach (['PRODI', 'AKADEMIK', 'KTU', 'DEKAN'] as $stage) {
        expect($letter->fresh()->current_stage)->toBe($stage);
        $service->decide($letter->fresh(), $superAdmin, 'FORWARD', null);
    }

    expect($letter->fresh()->current_stage)->toBe('RESEPSIONIS');
    $signatoryIds = collect($service->availableFinalSignatories($superAdmin, $letter->fresh()))->pluck('id')->all();
    expect($signatoryIds)->toContain($dekan->id)->not->toContain($superAdmin->id);

    $service->finalize($letter->fresh(), $superAdmin, [
        'letter_number' => '124/UN8.1/CA/2026',
        'signatory_id' => $dekan->id,
    ]);

    $finished = $letter->fresh();
    expect($finished->status)->toBe('ARCHIVED')
        ->and($finished->meta['signatory']['id'])->toBe($dekan->id);

    $this->actingAs($superAdmin)
        ->get(route('cuti-akademik.create'))
        ->assertOk();
});

test('a submission without supporting documents is allowed', function () {
    $student = cutiUser(1);
    $service = app(CutiAkademikService::class);
    $letter = $service->create($student, cutiPayload(['attachments' => []]));

    $service->ajuan($letter, $student);

    expect($letter->fresh())
        ->status->toBe('IN_PROGRESS')
        ->current_stage->toBe('PRODI');
});

test('the attachment service rejects files over the approved size limit', function () {
    $student = cutiUser(1);

    expect(fn () => app(CutiAkademikService::class)->create($student, cutiPayload([
        'attachments' => [UploadedFile::fake()->create('terlalu-besar.pdf', 101, 'application/pdf')],
    ])))->toThrow(ValidationException::class);

    expect(Letter::query()->count())->toBe(0);
});

test('the Cuti Akademik index is available only through authenticated module roles', function () {
    $student = cutiUser(1);

    $this->actingAs($student)
        ->get(route('cuti-akademik.index'))
        ->assertOk();
});

test('a mahasiswa can open their own Cuti Akademik draft for editing', function () {
    $student = cutiUser(1);
    $letter = app(CutiAkademikService::class)->create($student, cutiPayload());

    $this->actingAs($student)
        ->get(route('cuti-akademik.edit', $letter))
        ->assertOk();
});

test('an unchanged Cuti Akademik draft can be updated through Laravel method spoofing', function () {
    $student = cutiUser(1);
    $letter = app(CutiAkademikService::class)->create($student, cutiPayload());

    $this->actingAs($student)
        ->post(route('cuti-akademik.update', $letter), [
            ...cutiPayload(['attachments' => []]),
            '_method' => 'PUT',
        ])
        ->assertRedirect(route('cuti-akademik.show', $letter));

    expect($letter->fresh()->meta)->toMatchArray([
        'semester' => '5',
        'tahun_akademik' => '2026/2027',
        'nomor_hp' => '081234567890',
        'nominal_ukt' => '2500000',
    ]);
});
