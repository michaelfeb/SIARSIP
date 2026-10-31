<?php

use App\Models\Letter;
use App\Models\LetterType;
use App\Models\User;
use Database\Seeders\CutiAkademikLetterTypeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CutiAkademikLetterTypeSeeder::class);
});

function approvalWorkflowActor(int $roleId): User
{
    return User::factory()->create(['role_id' => $roleId]);
}

function approvalWorkflowType(): LetterType
{
    return LetterType::query()->where('code', 'CUTI_AKADEMIK')->firstOrFail();
}

test('akademik and super admin can open approval workflow settings', function () {
    foreach ([6, 8] as $roleId) {
        $this->actingAs(approvalWorkflowActor($roleId))
            ->get(route('approval-workflows.index'))
            ->assertOk();
    }

    $this->actingAs(approvalWorkflowActor(3))
        ->get(route('approval-workflows.index'))
        ->assertForbidden();
});

test('an administrator can update a letter type workflow and notes', function () {
    $type = approvalWorkflowType();

    $this->actingAs(approvalWorkflowActor(6))
        ->put(route('approval-workflows.update', $type), [
            'approval_chain' => ['PRODI', 'AKADEMIK', 'WD3', 'RESEPSIONIS'],
            'notes_markdown' => '## Panduan terbaru',
        ])
        ->assertRedirect(route('approval-workflows.index'));

    expect($type->fresh()->approval_chain)->toBe(['PRODI', 'AKADEMIK', 'WD3', 'RESEPSIONIS'])
        ->and($type->fresh()->notes_markdown)->toBe('## Panduan terbaru');
});

test('a workflow role must exist and cannot be duplicated', function () {
    $type = approvalWorkflowType();
    $admin = approvalWorkflowActor(8);

    $this->actingAs($admin)
        ->from(route('approval-workflows.index'))
        ->put(route('approval-workflows.update', $type), [
            'approval_chain' => ['PRODI', 'TIDAK ADA', 'RESEPSIONIS'],
            'notes_markdown' => null,
        ])
        ->assertRedirect(route('approval-workflows.index'))
        ->assertSessionHasErrors('approval_chain.1');

    $this->actingAs($admin)
        ->from(route('approval-workflows.index'))
        ->put(route('approval-workflows.update', $type), [
            'approval_chain' => ['PRODI', 'PRODI', 'RESEPSIONIS'],
            'notes_markdown' => null,
        ])
        ->assertRedirect(route('approval-workflows.index'))
        ->assertSessionHasErrors('approval_chain.1');
});

test('terminal resepsionis cannot be removed or moved', function () {
    $type = approvalWorkflowType();

    $this->actingAs(approvalWorkflowActor(6))
        ->from(route('approval-workflows.index'))
        ->put(route('approval-workflows.update', $type), [
            'approval_chain' => ['PRODI', 'RESEPSIONIS', 'DEKAN'],
            'notes_markdown' => null,
        ])
        ->assertRedirect(route('approval-workflows.index'))
        ->assertSessionHasErrors('approval_chain');

    expect($type->fresh()->approval_chain)->toBe(['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS']);
});

test('workflow and notes cannot change while the letter type has an active letter', function () {
    $type = approvalWorkflowType();
    $student = approvalWorkflowActor(1);
    Letter::query()->create([
        'type_id' => $type->id,
        'applicant_id' => $student->id,
        'applicant_name' => $student->nama,
        'applicant_id_number' => $student->nim ?? (string) $student->id,
        'category' => $type->category,
        'status' => 'IN_PROGRESS',
        'current_stage' => 'PRODI',
    ]);

    $this->actingAs(approvalWorkflowActor(8))
        ->from(route('approval-workflows.index'))
        ->put(route('approval-workflows.update', $type), [
            'approval_chain' => ['PRODI', 'WD3', 'RESEPSIONIS'],
            'notes_markdown' => 'Catatan baru',
        ])
        ->assertRedirect(route('approval-workflows.index'))
        ->assertSessionHasErrors('approval_chain');

    expect($type->fresh()->approval_chain)->toBe(['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS'])
        ->and($type->fresh()->notes_markdown)->not->toBe('Catatan baru');
});
