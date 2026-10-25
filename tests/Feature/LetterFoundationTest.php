<?php

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\LetterLog;
use App\Models\LetterRoute;
use App\Models\LetterType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeFoundationLetter(LetterType $letterType, User $applicant, array $overrides = []): Letter
{
    return Letter::query()->create([
        'type_id' => $letterType->id,
        'applicant_id' => $applicant->id,
        'applicant_name' => $applicant->nama,
        'applicant_id_number' => $applicant->nim ?? '2410517310016',
        'program_studi' => 'Ilmu Komputer',
        'category' => $letterType->category,
        'purpose' => 'Keperluan pengujian foundation surat.',
        'notes_snapshot' => 'Panduan permohonan.',
        'meta' => ['semester' => 5],
        ...$overrides,
    ]);
}

function makeFoundationType(): LetterType
{
    return LetterType::query()->create([
        'code' => 'FOUNDATION_TEST',
        'category' => 'AKADEMIK',
        'name' => 'Cuti Akademik',
        'notes_markdown' => 'Panduan akan ditentukan pada implementasi layanan.',
        'approval_chain' => ['PRODI', 'AKADEMIK'],
        'applicant_roles' => ['MAHASISWA'],
        'pdf_template' => 'letters.cuti-akademik',
    ]);
}

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('canonical roles have stable IDs and the approved Cuti Akademik type is seeded', function () {
    expect(Role::query()->orderBy('id')->pluck('nama', 'id')->all())->toBe([
        1 => 'MAHASISWA',
        2 => 'RESEPSIONIS',
        3 => 'DEKAN',
        4 => 'WD1',
        5 => 'KTU',
        6 => 'AKADEMIK',
        7 => 'BAGIAN LAYANAN',
        8 => 'SUPER ADMIN',
        9 => 'ADMIN TTPS',
        10 => 'WD2',
        11 => 'WD3',
        12 => 'PRODI',
    ])->and(LetterType::query()->where('code', 'CUTI_AKADEMIK')->exists())->toBeTrue();
});

test('the reusable letter tables are available', function () {
    foreach (['letter_types', 'letters', 'letter_attachments', 'letter_routes', 'letter_logs'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});

test('letters receive UUIDs and reject duplicate manual letter numbers', function () {
    $applicant = User::factory()->create(['role_id' => 1, 'nim' => '2410517310016']);
    $letterType = makeFoundationType();

    $firstLetter = makeFoundationLetter($letterType, $applicant, [
        'letter_number' => '/UN8.1.23/CUTI_AKADEMIK/2026/001',
    ]);
    $secondLetter = makeFoundationLetter($letterType, $applicant);

    expect(Str::isUuid($firstLetter->id))->toBeTrue()
        ->and($secondLetter->id)->not->toBe($firstLetter->id)
        ->and($secondLetter->letter_number)->toBeNull()
        ->and($secondLetter->meta)->toBe(['semester' => 5]);

    expect(fn () => makeFoundationLetter($letterType, $applicant, [
        'letter_number' => '/UN8.1.23/CUTI_AKADEMIK/2026/001',
    ]))->toThrow(QueryException::class);
});

test('letter types are restricted while letter child data cascades and user references become null', function () {
    $applicant = User::factory()->create(['role_id' => 1, 'nim' => '2410517310017']);
    $letterType = makeFoundationType();
    $letter = makeFoundationLetter($letterType, $applicant);

    $attachment = LetterAttachment::query()->create([
        'letter_id' => $letter->id,
        'file_path' => 'letters/attachments/2026/user-1/letter-example/document.pdf',
        'original_name' => 'document.pdf',
        'mime' => 'application/pdf',
        'size_bytes' => 1024,
        'uploaded_by' => $applicant->id,
    ]);
    $route = LetterRoute::query()->create([
        'letter_id' => $letter->id,
        'to_stage' => 'PRODI',
        'decided_by' => $applicant->id,
    ]);
    $log = LetterLog::query()->create([
        'letter_id' => $letter->id,
        'actor_id' => $applicant->id,
        'action' => 'CREATE_DRAFT',
        'meta' => ['source' => 'test'],
    ]);

    expect(fn () => $letterType->delete())->toThrow(QueryException::class);

    $applicant->delete();

    expect($letter->fresh()->applicant_id)->toBeNull()
        ->and($attachment->fresh()->uploaded_by)->toBeNull()
        ->and($route->fresh()->decided_by)->toBeNull()
        ->and($log->fresh()->actor_id)->toBeNull();

    $letter->delete();

    expect(LetterAttachment::query()->find($attachment->id))->toBeNull()
        ->and(LetterRoute::query()->find($route->id))->toBeNull()
        ->and(LetterLog::query()->find($log->id))->toBeNull();
});
