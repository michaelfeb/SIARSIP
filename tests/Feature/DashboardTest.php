<?php

use App\Models\BerkasSidangNol;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\User;
use Database\Seeders\CutiAkademikLetterTypeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CutiAkademikLetterTypeSeeder::class);
});

function dashboardUser(int $roleId, ?int $programStudi = null): User
{
    return User::factory()->create([
        'role_id' => $roleId,
        'program_studi' => $programStudi,
    ]);
}

function createDashboardSidangNol(User $user, int $status, string $submittedAt): BerkasSidangNol
{
    return BerkasSidangNol::query()->create([
        'user_id' => $user->id,
        'dokumen_hasil_studi' => 'sidang-nol/hasil-studi.pdf',
        'dokumen_data_diri' => 'sidang-nol/data-diri.pdf',
        'dokumen_pddikti_ukt' => 'sidang-nol/pddikti-ukt.pdf',
        'dokumen_ruangbaca_laboratorium_pkkmb_skpi' => 'sidang-nol/ruang-baca.pdf',
        'dokumen_office_toefl' => 'sidang-nol/office-toefl.pdf',
        'status' => $status,
        'tanggal_dikirim' => $submittedAt,
    ]);
}

function createDashboardCutiAkademik(User $user, string $status, string $submittedAt): Letter
{
    return Letter::query()->create([
        'type_id' => LetterType::query()->where('code', 'CUTI_AKADEMIK')->valueOrFail('id'),
        'applicant_id' => $user->id,
        'applicant_name' => $user->nama,
        'applicant_id_number' => $user->nim ?? (string) $user->id,
        'program_studi' => match ($user->program_studi) {
            1 => 'Matematika',
            2 => 'Kimia',
            3 => 'Biologi',
            default => 'Tidak Diketahui',
        },
        'category' => 'Akademik',
        'status' => $status,
        'submitted_at' => $submittedAt,
    ]);
}

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = dashboardUser(2);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('the dashboard combines submitted Sidang Nol and Cuti Akademik records', function () {
    $staff = dashboardUser(2);
    $matematika = dashboardUser(1, 1);
    $kimia = dashboardUser(1, 2);
    $biologi = dashboardUser(1, 3);

    createDashboardSidangNol($matematika, 1, '2026-10-02');
    createDashboardSidangNol($kimia, 2, '2026-10-03');
    createDashboardSidangNol($biologi, 3, '2026-09-04');
    createDashboardSidangNol($matematika, 0, '2026-10-05');

    createDashboardCutiAkademik($matematika, 'IN_PROGRESS', '2026-10-06');
    createDashboardCutiAkademik($kimia, 'ARCHIVED', '2026-10-07');
    createDashboardCutiAkademik($biologi, 'REJECTED', '2026-09-08');
    createDashboardCutiAkademik($matematika, 'DRAFT', '2026-10-09');

    $this->actingAs($staff)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/index')
            ->where('summary', [
                'total' => 6,
                'inProgress' => 2,
                'archived' => 2,
                'rejected' => 2,
            ])
            ->has('programStudiDistribution', 3)
            ->where('filterOptions.years.0', 2026));
});

test('dashboard filters apply to both letter sources', function () {
    $staff = dashboardUser(2);
    $matematika = dashboardUser(1, 1);
    $kimia = dashboardUser(1, 2);

    createDashboardSidangNol($matematika, 1, '2026-10-02');
    createDashboardSidangNol($kimia, 2, '2026-09-03');
    createDashboardCutiAkademik($matematika, 'IN_PROGRESS', '2026-10-04');
    createDashboardCutiAkademik($kimia, 'ARCHIVED', '2026-10-05');

    $this->actingAs($staff)
        ->get(route('dashboard', [
            'letter_type' => 'cuti-akademik',
            'month' => 10,
            'year' => 2026,
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('summary', [
                'total' => 2,
                'inProgress' => 1,
                'archived' => 1,
                'rejected' => 0,
            ])
            ->has('programStudiDistribution', 2));
});

test('dashboard returns zero totals when the filters have no matching records', function () {
    $staff = dashboardUser(2);

    $this->actingAs($staff)
        ->get(route('dashboard', ['month' => 12, 'year' => 2026]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('summary', [
                'total' => 0,
                'inProgress' => 0,
                'archived' => 0,
                'rejected' => 0,
            ])
            ->has('programStudiDistribution', 0));
});

test('students remain redirected to Berkas Sidang Nol', function () {
    $this->actingAs(dashboardUser(1))
        ->get(route('dashboard'))
        ->assertRedirect(route('berkas-sidang-nol.index'));
});

test('invalid dashboard filters are rejected', function () {
    $this->actingAs(dashboardUser(2))
        ->get(route('dashboard', ['month' => 13]))
        ->assertSessionHasErrors('month');
});
