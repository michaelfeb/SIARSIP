<?php

namespace App\Services;

use App\Models\TemplateSurat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TemplateSuratService
{
    /**
     * Return a filtered, paginated list of letter templates.
     *
     * @param  array{search?: string|null, per_page?: int}  $filters
     */
    public function datatable(array $filters, bool $publishedOnly = false): LengthAwarePaginator
    {
        return TemplateSurat::query()
            ->when($publishedOnly, fn ($query) => $query->where('status', true))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('nama', 'like', "%{$search}%"))
            ->orderByDesc('tanggal_publish')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    /**
     * Create a letter template and store its uploaded document.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TemplateSurat
    {
        [$data, $newPath] = $this->prepareDocumentData($data);

        try {
            return TemplateSurat::query()->create($data);
        } catch (Throwable $exception) {
            $this->deleteStoredDocument($newPath);

            throw $exception;
        }
    }

    /**
     * Update a letter template and replace its document when a new one is supplied.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(TemplateSurat $templateSurat, array $data): TemplateSurat
    {
        $previousDocumentPath = $templateSurat->dokumen_path;
        [$data, $newDocumentPath] = $this->prepareDocumentData($data);

        try {
            $templateSurat->update($data);
        } catch (Throwable $exception) {
            $this->deleteStoredDocument($newDocumentPath);

            throw $exception;
        }

        if ($newDocumentPath !== null && $previousDocumentPath !== $newDocumentPath) {
            $this->deleteStoredDocument($previousDocumentPath);
        }

        return $templateSurat;
    }

    public function delete(TemplateSurat $template): void
    {
        $path = $template->dokumen_path;
        $template->delete();
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Download the document stored for a letter template.
     */
    public function download(TemplateSurat $templateSurat): StreamedResponse
    {
        return Storage::disk('public')->download($templateSurat->dokumen_path, $templateSurat->nama.'.docx');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{array<string, mixed>, string|null}
     */
    private function prepareDocumentData(array $data): array
    {
        $document = $data['dokumen_path'] ?? null;

        if ($document instanceof UploadedFile) {
            $documentPath = $document->store('template_surat', 'public');
            $data['dokumen_path'] = $documentPath;

            return [$data, $documentPath];
        }

        unset($data['dokumen_path']);

        return [$data, null];
    }

    private function deleteStoredDocument(?string $documentPath): void
    {
        if ($documentPath !== null) {
            Storage::disk('public')->delete($documentPath);
        }
    }
}
