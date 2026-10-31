<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CutiAkademikService extends LetterService
{
    private const TYPE_CODE = 'CUTI_AKADEMIK';

    /** @var array<int, string> */
    private const PROGRAM_STUDI = [
        1 => 'Matematika',
        2 => 'Kimia',
        3 => 'Biologi',
        4 => 'Fisika',
        5 => 'Farmasi',
        6 => 'Ilmu Komputer',
        7 => 'Statistika',
        8 => 'Profesi Apoteker',
    ];

    public function __construct(
        LetterAttachmentService $attachments,
        PdfGeneratorService $pdfGenerator,
        private readonly LetterQrCodeService $qrCode,
    ) {
        parent::__construct($attachments, $pdfGenerator);
    }

    protected function letterTypeCode(): string
    {
        return self::TYPE_CODE;
    }

    protected function finalizationRole(): string
    {
        return 'RESEPSIONIS';
    }

    protected function signatoryRole(): string
    {
        $chain = $this->letterType()->approval_chain ?? [];
        $resepsionisIndex = array_key_last($chain);
        $signatoryRole = $resepsionisIndex === null ? null : ($chain[$resepsionisIndex - 1] ?? null);

        if ($resepsionisIndex === null || $chain[$resepsionisIndex] !== 'RESEPSIONIS' || $signatoryRole === null) {
            throw new \LogicException('Alur Cuti Akademik harus memiliki penandatangan sebelum RESEPSIONIS.');
        }

        return $signatoryRole;
    }

    protected function pdfView(): string
    {
        return 'letters.cuti-akademik';
    }

    /**
     * Return server-owned guide and approval data for the create/edit form.
     *
     * @return array{notesMarkdown: string|null, notesHtml: string, approvalChain: array<int, string>}
     */
    public function formContext(): array
    {
        $type = $this->letterType();
        $notesMarkdown = $type->notes_markdown;

        return [
            'notesMarkdown' => $notesMarkdown,
            'notesHtml' => $notesMarkdown === null
                ? ''
                : Str::markdown($notesMarkdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
            'approvalChain' => $type->approval_chain ?? [],
        ];
    }

    /**
     * @param  array{id: int, name: string, role: string, nim: string|null}  $signatory
     * @return array<string, mixed>
     */
    protected function pdfData(Letter $letter, array $signatory): array
    {
        return [
            'letter' => $letter,
            'meta' => $letter->meta ?? [],
            'signatory' => $signatory,
            'signatoryQrCode' => $this->qrCode->forLetter($letter),
        ];
    }

    /** @param array<string, mixed> $data */
    public function preview(User $actor, array $data): string
    {
        $this->ensureCanCreate($actor);
        $type = $this->letterType();
        $letter = new Letter([
            'type_id' => $type->id,
            'applicant_id' => $actor->id,
            'applicant_name' => $actor->nama,
            'applicant_id_number' => $actor->nim ?? (string) $actor->id,
            'program_studi' => $this->programStudiLabel($actor->program_studi),
            'category' => $type->category,
            'purpose' => $data['alasan_cuti'],
            'status' => 'DRAFT',
            'issued_at' => now()->toDateString(),
            'meta' => $this->buildMeta($actor, $data),
        ]);
        $letter->setRelation('letterType', $type);
        $pdfData = $this->pdfData($letter, ['id' => 0, 'name' => 'Akan ditetapkan setelah persetujuan', 'nim' => null, 'role' => $this->signatoryRole()]);
        $pdfData['isPreview'] = true;

        return $this->pdfGenerator->output($this->pdfView(), $pdfData, $this->pdfPaper(), $this->pdfOrientation());
    }

    /**
     * Return the Cuti Akademik letters visible to an actor as a filtered, paginated list.
     *
     * @param  array{search: string|null, status: string|null, per_page: int}  $filters
     */
    public function datatable(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Letter::query()
            ->with(['applicant', 'letterType'])
            ->where('type_id', $this->letterType()->id);

        $this->applyActorVisibility($query, $actor);

        return $query
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('applicant_name', 'like', "%{$search}%")
                        ->orWhere('applicant_id_number', 'like', "%{$search}%")
                        ->orWhere('letter_number', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    /**
     * @return array{finalizationRole: string, defaultSignatoryRole: string, signatoryRoles: array<int, string>}
     */
    public function finalizationContext(Letter $letter): array
    {
        $this->ensureLetterType($letter);

        return [
            'finalizationRole' => $this->finalizationRole(),
            'defaultSignatoryRole' => $this->signatoryRole(),
            'signatoryRoles' => $this->availableFinalSignatoryRoles($letter),
        ];
    }

    /**
     * Create a Cuti Akademik letter in its initial DRAFT state.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, array $data): Letter
    {
        $this->ensureCanCreate($actor);
        $type = $this->letterType();

        return DB::transaction(function () use ($actor, $data, $type): Letter {
            $letter = Letter::query()->create([
                'type_id' => $type->id,
                'applicant_id' => $actor->id,
                'applicant_name' => $actor->nama,
                'applicant_id_number' => $actor->nim ?? (string) $actor->id,
                'program_studi' => $this->programStudiLabel($actor->program_studi),
                'category' => $type->category,
                'purpose' => $data['alasan_cuti'],
                'status' => 'DRAFT',
                'notes_snapshot' => $type->notes_markdown,
                'meta' => $this->buildMeta($actor, $data),
            ]);

            $this->attachments->store($letter, $data['attachments'] ?? [], $actor);
            $this->logWorkflowAction($letter, $actor, 'CREATE_DRAFT');

            return $letter->load('attachments');
        });
    }

    /**
     * Update the data and attachments of an applicant-owned Cuti Akademik draft.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Letter $letter, User $actor, array $data): Letter
    {
        $this->ensureLetterType($letter);
        $this->ensureDraftOwner($letter, $actor);

        return DB::transaction(function () use ($letter, $actor, $data): Letter {
            $letter->update([
                'purpose' => $data['alasan_cuti'],
                'meta' => $this->buildMeta($actor, $data, $letter),
            ]);
            $this->attachments->store($letter, $data['attachments'] ?? [], $actor);
            $this->logWorkflowAction($letter, $actor, 'UPDATE_DRAFT');

            return $letter->fresh(['attachments']);
        });
    }

    private function ensureCanCreate(User $actor): void
    {
        if (! $this->isApplicant($actor) && ! $this->isSuperAdmin($actor)) {
            throw new AuthorizationException;
        }
    }

    protected function ensureReviewerScope(Letter $letter, User $actor): void
    {
        if ($letter->current_stage === 'PRODI' && (string) ($letter->meta['program_studi_id'] ?? '') !== (string) $actor->program_studi) {
            throw new AuthorizationException;
        }
    }

    protected function applyReviewerVisibility(Builder $query, User $actor, ?string $role): void
    {
        if ($role === 'PRODI') {
            $query->where('meta->program_studi_id', $actor->program_studi);
        }
    }

    /** @param array<string, mixed> $data */
    private function buildMeta(User $actor, array $data, ?Letter $letter = null): array
    {
        return [
            'semester' => $data['semester'],
            'tahun_akademik' => $data['tahun_akademik'],
            'nomor_hp' => $data['nomor_hp'],
            'nominal_ukt' => $data['nominal_ukt'],
            'alasan_cuti' => $data['alasan_cuti'],
            'program_studi_id' => $this->isSuperAdmin($actor) && $letter !== null
                ? $letter->meta['program_studi_id'] ?? null
                : $actor->program_studi,
        ];
    }

    private function programStudiLabel(?int $programStudi): ?string
    {
        return $programStudi === null ? null : (self::PROGRAM_STUDI[$programStudi] ?? 'Tidak Diketahui');
    }
}
