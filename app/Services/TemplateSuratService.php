<?php

namespace App\Services;

use App\Models\TemplateSurat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TemplateSuratService
{
    public function paginate(array $filters, bool $publishedOnly = false): LengthAwarePaginator
    {
        return TemplateSurat::query()
            ->when($publishedOnly, fn($query) => $query->where('status', true))
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where('nama', 'like', "%{$search}%"))
            ->orderByDesc('tanggal_publish')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function save(array $validated, ?TemplateSurat $template = null): TemplateSurat
    {
        $oldPath = $template?->dokumen_path;
        $newPath = null;
        $data = $validated;

        if (($validated['dokumen_path'] ?? null) instanceof UploadedFile) {
            $newPath = $validated['dokumen_path']->store('template_surat', 'public');
            $data['dokumen_path'] = $newPath;
        } else {
            unset($data['dokumen_path']);
        }

        try {
            $template ??= new TemplateSurat();
            $template->fill($data)->save();
        } catch (Throwable $exception) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) Storage::disk('public')->delete($oldPath);

        return $template;
    }

    public function delete(TemplateSurat $template): void
    {
        $path = $template->dokumen_path;
        $template->delete();
        if ($path) Storage::disk('public')->delete($path);
    }

    public function download(TemplateSurat $template)
    {
        return Storage::disk('public')->download($template->dokumen_path, $template->nama . '.docx');
    }
}
