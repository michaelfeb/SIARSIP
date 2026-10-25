<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LetterAttachmentService
{
    /** @param array<int, UploadedFile> $files */
    public function store(Letter $letter, array $files, User $actor): void
    {
        if ($files === []) {
            return;
        }

        $existingCount = $letter->attachments()->count();

        if (($existingCount + count($files)) > 5) {
            throw ValidationException::withMessages([
                'attachments' => 'Maksimal lima dokumen pendukung dapat diunggah.',
            ]);
        }

        foreach ($files as $file) {
            $mime = $file->getMimeType() ?? $file->getClientMimeType();
            if (! $file->isValid() || ! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true) || ($file->getSize() ?? 0) > 102400) {
                throw ValidationException::withMessages([
                    'attachments' => 'Dokumen harus berupa PDF, JPG, JPEG, atau PNG dengan ukuran maksimal 100 KB.',
                ]);
            }

            $extension = strtolower($file->extension());
            $path = sprintf(
                'letters/attachments/%s/user-%s/letter-%s/%s.%s',
                now()->format('Y'),
                $letter->applicant_id ?? 'historical',
                $letter->id,
                (string) Str::uuid(),
                $extension,
            );

            Storage::disk('secure')->putFileAs(dirname($path), $file, basename($path));

            $letter->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $mime,
                'size_bytes' => $file->getSize(),
                'uploaded_by' => $actor->id,
            ]);
        }
    }

    public function deleteAll(Letter $letter): void
    {
        $attachments = $letter->attachments()->get();

        foreach ($attachments as $attachment) {
            Storage::disk('secure')->delete($attachment->file_path);
        }

        $letter->attachments()->delete();
    }

    public function exists(LetterAttachment $attachment): bool
    {
        return Storage::disk('secure')->exists($attachment->file_path);
    }
}
