<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\LetterType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

abstract class LetterService
{
    private ?LetterType $resolvedLetterType = null;

    public function __construct(
        protected readonly LetterAttachmentService $attachments,
        protected readonly PdfGeneratorService $pdfGenerator,
    ) {}

    abstract protected function letterTypeCode(): string;

    abstract protected function finalizationRole(): string;

    abstract protected function signatoryRole(): string;

    /**
     * Return the Blade view used to render this Letter Type's final PDF.
     */
    abstract protected function pdfView(): string;

    /**
     * Build the saved letter data passed to this Letter Type's PDF Blade view.
     *
     * @param  array{id: int, name: string, role: string, nim: string|null}  $signatory
     * @return array<string, mixed>
     */
    abstract protected function pdfData(Letter $letter, array $signatory): array;

    /**
     * Load a letter for a permitted applicant, reviewer, or administrator.
     */
    public function findForDisplay(Letter $letter, User $actor): Letter
    {
        $this->ensureLetterType($letter);
        $this->ensureCanView($letter, $actor);

        return $letter->load(['attachments', 'routes.decider', 'logs.actor', 'letterType']);
    }

    /**
     * Return the configured workflow shape used by a Letter detail page.
     *
     * The active stage and route history remain on the Letter itself. This
     * context deliberately contains only Letter Type configuration so every
     * module presents the same server-owned approval sequence.
     *
     * @return array{approvalChain: array<int, string>}
     */
    public function workflowDisplayContext(Letter $letter): array
    {
        $this->ensureLetterType($letter);

        return [
            'approvalChain' => $this->approvalChain($letter),
        ];
    }

    /**
     * Resolve an attachment only when it belongs to a letter the actor may view.
     */
    public function findViewableAttachment(Letter $letter, LetterAttachment $attachment, User $actor): LetterAttachment
    {
        $this->ensureLetterType($letter);
        $this->ensureCanView($letter, $actor);

        if ($attachment->letter_id !== $letter->id) {
            abort(404);
        }

        return $attachment;
    }

    /**
     * Delete an applicant-owned draft and every private attachment associated with it.
     */
    public function delete(Letter $letter, User $actor): void
    {
        $this->ensureLetterType($letter);
        $this->ensureDraftOwner($letter, $actor);

        $this->attachments->deleteAll($letter);
        $letter->delete();
    }

    /**
     * Send an eligible draft to the first configured approval stage.
     */
    public function ajuan(Letter $letter, User $actor): void
    {
        $this->ensureLetterType($letter);
        $this->ensureDraftOwner($letter, $actor);

        DB::transaction(function () use ($letter, $actor): void {
            $locked = $this->findLockedLetter($letter->id);
            $this->ensureDraftOwner($locked, $actor);

            if ($locked->attachments()->count() < $this->minimumRequiredAttachments()) {
                throw ValidationException::withMessages([
                    'attachments' => 'Minimal satu dokumen pendukung wajib diunggah sebelum dikirim.',
                ]);
            }

            $stage = $this->approvalChain($locked)[0] ?? null;
            if ($stage === null) {
                throw ValidationException::withMessages([
                    'approval_chain' => 'Alur persetujuan surat belum dikonfigurasi.',
                ]);
            }

            $locked->update([
                'status' => 'IN_PROGRESS',
                'current_stage' => $stage,
                'submitted_at' => now(),
            ]);
            $locked->routes()->create(['from_stage' => null, 'to_stage' => $stage, 'decided_by' => $actor->id]);
            $this->logWorkflowAction($locked, $actor, 'SUBMIT', ['to_stage' => $stage]);
        });
    }

    /**
     * Record a current-stage rejection or forward a letter to its immediate next stage.
     */
    public function decide(Letter $letter, User $actor, string $decision, ?string $note): void
    {
        $this->ensureLetterType($letter);

        DB::transaction(function () use ($letter, $actor, $decision, $note): void {
            $locked = $this->findLockedLetter($letter->id);
            $this->ensureCurrentStageReviewer($locked, $actor);

            if ($decision === 'REJECT') {
                $locked->update(['status' => 'REJECTED', 'current_stage' => null]);
                $locked->routes()->create([
                    'from_stage' => $locked->getOriginal('current_stage'),
                    'to_stage' => 'REJECTED',
                    'decided_by' => $actor->id,
                    'note' => $note,
                ]);
                $this->logWorkflowAction($locked, $actor, 'REJECT', ['stage' => $locked->getOriginal('current_stage'), 'note' => $note]);

                return;
            }

            $fromStage = $locked->current_stage;
            $nextStage = $this->nextApprovalStage($locked, $fromStage);
            if ($nextStage === null) {
                throw ValidationException::withMessages(['decision' => 'Tahap ini harus difinalisasi oleh petugas terakhir.']);
            }

            $locked->update(['current_stage' => $nextStage]);
            $locked->routes()->create([
                'from_stage' => $fromStage,
                'to_stage' => $nextStage,
                'decided_by' => $actor->id,
                'note' => $note,
            ]);
            $this->logWorkflowAction($locked, $actor, 'FORWARD', ['from_stage' => $fromStage, 'to_stage' => $nextStage, 'note' => $note]);
        });
    }

    /**
     * Return users eligible to sign a final letter at the final approval stage.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function availableFinalSignatories(User $actor, Letter $letter): array
    {
        $this->ensureLetterType($letter);
        $this->ensureFinalizationStage($letter, $actor);

        return User::query()->with('role')->get()
            ->filter(fn (User $user) => $this->roleName($user) === $this->signatoryRole())
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->nama])
            ->values()
            ->all();
    }

    /**
     * Determine whether the letter is currently at this module's configured finalization stage.
     */
    public function isFinalizationStage(Letter $letter): bool
    {
        $this->ensureLetterType($letter);

        return $letter->current_stage === $this->finalizationRole();
    }

    /**
     * Render, store, and archive an approved letter using its Letter Type PDF configuration.
     *
     * @param  array{letter_number: string, signatory_id: int, note?: string|null}  $data
     */
    public function finalize(Letter $letter, User $actor, array $data): void
    {
        $this->ensureLetterType($letter);
        $generatedPath = null;
        $isPdfOperation = false;

        try {
            DB::transaction(function () use ($letter, $actor, $data, &$generatedPath, &$isPdfOperation): void {
                $locked = $this->findLockedLetter($letter->id);
                $this->ensureFinalizationStage($locked, $actor);

                $signatory = User::query()->with('role')->findOrFail($data['signatory_id']);
                if ($this->roleName($signatory) !== $this->signatoryRole()) {
                    throw ValidationException::withMessages([
                        'signatory_id' => sprintf('Penandatangan harus memiliki peran %s.', $this->signatoryRole()),
                    ]);
                }

                if (Letter::query()->where('letter_number', $data['letter_number'])->whereKeyNot($locked->id)->exists()) {
                    throw ValidationException::withMessages(['letter_number' => 'Nomor surat sudah digunakan.']);
                }

                $signatorySnapshot = [
                    'id' => $signatory->id,
                    'name' => $signatory->nama,
                    'nim' => $signatory->nim,
                    'role' => $this->signatoryRole(),
                ];
                $locked->load('letterType');
                $locked->letter_number = $data['letter_number'];
                $locked->issued_at = now()->toDateString();
                $generatedPath = $this->finalPdfPath($locked);
                $isPdfOperation = true;

                $output = $this->pdfGenerator->output(
                    $this->pdfView(),
                    $this->pdfData($locked, $signatorySnapshot),
                    $this->pdfPaper(),
                    $this->pdfOrientation(),
                );
                Storage::disk('secure')->put($generatedPath, $output);

                $meta = $locked->meta ?? [];
                $meta['signatory'] = $signatorySnapshot;
                $locked->update([
                    'letter_number' => $data['letter_number'],
                    'issued_at' => $locked->issued_at,
                    'final_pdf_path' => $generatedPath,
                    'archived_at' => now(),
                    'status' => 'ARCHIVED',
                    'current_stage' => null,
                    'meta' => $meta,
                    'pdf_error' => false,
                    'pdf_error_meta' => null,
                ]);
                $locked->routes()->create([
                    'from_stage' => $this->finalizationRole(),
                    'to_stage' => 'ARCHIVED',
                    'decided_by' => $actor->id,
                    'note' => $data['note'] ?? null,
                ]);
                $this->logWorkflowAction($locked, $actor, 'FINALISE', ['letter_number' => $data['letter_number'], 'signatory_id' => $signatory->id]);
            });
        } catch (\Throwable $exception) {
            if ($generatedPath !== null) {
                Storage::disk('secure')->delete($generatedPath);
            }

            if ($isPdfOperation) {
                Letter::query()->whereKey($letter->id)->update([
                    'pdf_error' => true,
                    'pdf_error_meta' => ['message' => 'PDF generation failed.', 'at' => now()->toIso8601String()],
                ]);
            }

            throw $exception;
        }
    }

    protected function letterType(): LetterType
    {
        return $this->resolvedLetterType ??= LetterType::query()
            ->where('code', $this->letterTypeCode())
            ->firstOrFail();
    }

    protected function ensureLetterType(Letter $letter): void
    {
        if ($letter->type_id !== $this->letterType()->id) {
            abort(404);
        }
    }

    protected function findLockedLetter(string $id): Letter
    {
        $letter = Letter::query()->with('letterType')->lockForUpdate()->findOrFail($id);
        $this->ensureLetterType($letter);

        return $letter;
    }

    protected function ensureDraftOwner(Letter $letter, User $actor): void
    {
        if ($letter->status !== 'DRAFT') {
            throw new AuthorizationException;
        }

        if ($this->isSuperAdmin($actor)) {
            return;
        }

        if (! $this->isApplicant($actor) || $letter->applicant_id !== $actor->id) {
            throw new AuthorizationException;
        }
    }

    protected function ensureCurrentStageReviewer(Letter $letter, User $actor): void
    {
        if ($letter->status !== 'IN_PROGRESS') {
            throw new AuthorizationException;
        }

        if ($this->isSuperAdmin($actor)) {
            return;
        }

        if ($letter->current_stage !== $this->roleName($actor)) {
            throw new AuthorizationException;
        }

        $this->ensureReviewerScope($letter, $actor);
    }

    /**
     * Confirm that the actor owns the configured final stage and that the Letter Type chain is valid.
     */
    protected function ensureFinalizationStage(Letter $letter, User $actor): void
    {
        $this->ensureCurrentStageReviewer($letter, $actor);

        $chain = $this->approvalChain($letter);
        $lastApprovalStage = $chain[array_key_last($chain)] ?? null;
        if ($lastApprovalStage !== $this->finalizationRole()) {
            throw new \LogicException(sprintf(
                'Letter Type %s must end its approval chain with finalization role %s.',
                $this->letterTypeCode(),
                $this->finalizationRole(),
            ));
        }

        if ($letter->current_stage !== $this->finalizationRole()) {
            throw new AuthorizationException;
        }
    }

    protected function ensureCanView(Letter $letter, User $actor): void
    {
        if ($this->isApplicant($actor) && $letter->applicant_id === $actor->id) {
            return;
        }

        if ($this->isSuperAdmin($actor)) {
            return;
        }

        if ($letter->status === 'ARCHIVED' && $this->roleName($actor) === $this->finalizationRole()) {
            return;
        }

        $this->ensureCurrentStageReviewer($letter, $actor);
    }

    protected function applyActorVisibility(Builder $query, User $actor): void
    {
        $role = $this->roleName($actor);

        if ($this->isApplicant($actor)) {
            $query->where('applicant_id', $actor->id);

            return;
        }

        if ($this->isSuperAdmin($actor)) {
            return;
        }

        $query->where('status', 'IN_PROGRESS')->where('current_stage', $role);
        $this->applyReviewerVisibility($query, $actor, $role);
    }

    protected function nextApprovalStage(Letter $letter, string $currentStage): ?string
    {
        $chain = $this->approvalChain($letter);
        $index = array_search($currentStage, $chain, true);

        return $index === false ? null : ($chain[$index + 1] ?? null);
    }

    /** @return array<int, string> */
    protected function approvalChain(Letter $letter): array
    {
        return $letter->letterType?->approval_chain ?? $this->letterType()->approval_chain ?? [];
    }

    /** @param array<string, mixed> $meta */
    protected function logWorkflowAction(Letter $letter, User $actor, string $action, array $meta = []): void
    {
        $letter->logs()->create(['actor_id' => $actor->id, 'action' => $action, 'meta' => $meta]);
    }

    protected function roleName(User $user): ?string
    {
        return $user->relationLoaded('role') ? $user->role?->nama : $user->role()->value('nama');
    }

    protected function minimumRequiredAttachments(): int
    {
        return 0;
    }

    protected function pdfPaper(): string
    {
        return 'a4';
    }

    protected function pdfOrientation(): string
    {
        return 'portrait';
    }

    protected function finalPdfPath(Letter $letter): string
    {
        return sprintf(
            'letters/final/%s/%s/%s-%s.pdf',
            now()->format('Y'),
            now()->format('m'),
            $letter->id,
            $this->letterTypeCode(),
        );
    }

    protected function isApplicant(User $actor): bool
    {
        return $this->roleName($actor) === 'MAHASISWA';
    }

    protected function isSuperAdmin(User $actor): bool
    {
        return $this->roleName($actor) === 'SUPER ADMIN';
    }

    protected function ensureReviewerScope(Letter $letter, User $actor): void {}

    protected function applyReviewerVisibility(Builder $query, User $actor, ?string $role): void {}
}
