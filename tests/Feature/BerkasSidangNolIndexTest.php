<?php

use App\Models\BerkasSidangNol;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    foreach ([1 => 'Mahasiswa', 6 => 'Bagian Akademik'] as $id => $nama) {
        $role = new Role(['nama' => $nama]);
        $role->id = $id;
        $role->save();
    }
});

function createSidangNol(User $user, int $status, ?string $nomorSurat = null): BerkasSidangNol
{
    return BerkasSidangNol::query()->create([
        'user_id' => $user->id,
        'status' => $status,
        'nomor_surat' => $nomorSurat,
        'tanggal_dikirim' => now()->toDateString(),
    ]);
}

test('staff can filter their visible sidang nol records by status', function () {
    $staff = User::factory()->create(['role_id' => 6]);
    $student = User::factory()->create(['role_id' => 1]);
    $waiting = createSidangNol($student, 1, 'SN-001');
    createSidangNol($student, 2, 'SN-002');
    createSidangNol($student, 0, 'SN-003');

    $response = $this->actingAs($staff)->getJson(route('berkas-sidang-nol.index', ['status' => 1]));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $waiting->id)
        ->assertJsonPath('data.0.status', 1);
});

test('staff cannot use the status filter to see drafts', function () {
    $staff = User::factory()->create(['role_id' => 6]);
    $student = User::factory()->create(['role_id' => 1]);
    createSidangNol($student, 0, 'SN-DRAFT');

    $response = $this->actingAs($staff)->getJson(route('berkas-sidang-nol.index', ['status' => 0]));

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('students only see their own records when filtering by status', function () {
    $student = User::factory()->create(['role_id' => 1]);
    $otherStudent = User::factory()->create(['role_id' => 1]);
    $ownDraft = createSidangNol($student, 0, 'SN-OWN');
    createSidangNol($otherStudent, 0, 'SN-OTHER');

    $response = $this->actingAs($student)->getJson(route('berkas-sidang-nol.index', ['status' => 0]));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownDraft->id);
});

test('the sidang nol index searches student names and letter numbers', function () {
    $staff = User::factory()->create(['role_id' => 6]);
    $student = User::factory()->create(['role_id' => 1, 'nama' => 'Alya Sidang Nol']);
    $byName = createSidangNol($student, 1, 'SN-ALYA');
    createSidangNol($student, 2, 'SN-LAIN');

    $this->actingAs($staff)
        ->getJson(route('berkas-sidang-nol.index', ['search' => 'Alya']))
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->actingAs($staff)
        ->getJson(route('berkas-sidang-nol.index', ['search' => 'SN-ALYA']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $byName->id);
});

test('the sidang nol index rejects an unknown status filter', function () {
    $staff = User::factory()->create(['role_id' => 6]);

    $this->actingAs($staff)
        ->getJson(route('berkas-sidang-nol.index', ['status' => 99]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});
