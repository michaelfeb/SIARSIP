<?php

namespace App\Services;

use App\Models\Carousel;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CarouselService
{
    /**
     * @param  array{search: string|null, per_page: int}  $filters
     */
    public function datatable(array $filters): LengthAwarePaginator
    {
        return Carousel::query()
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orderByDesc('tanggal_publish')
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    /** @return Collection<int, Carousel> */
    public function publishedForLanding(): Collection
    {
        return Carousel::query()
            ->where('status', true)
            ->orderByDesc('tanggal_publish')
            ->orderByDesc('id')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): Carousel
    {
        $this->ensureCanManage($actor);
        [$data, $newImagePath] = $this->prepareImageData($data);

        try {
            return Carousel::query()->create($data);
        } catch (Throwable $exception) {
            $this->deleteStoredImage($newImagePath);

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Carousel $carousel, User $actor, array $data): Carousel
    {
        $this->ensureCanManage($actor);
        $previousImagePath = $carousel->gambar;
        [$data, $newImagePath] = $this->prepareImageData($data);

        try {
            $carousel->update($data);
        } catch (Throwable $exception) {
            $this->deleteStoredImage($newImagePath);

            throw $exception;
        }

        if ($newImagePath !== null && $previousImagePath !== $newImagePath) {
            $this->deleteStoredImage($previousImagePath);
        }

        return $carousel;
    }

    public function toggleStatus(Carousel $carousel, User $actor): Carousel
    {
        $this->ensureCanManage($actor);
        $carousel->update(['status' => ! $carousel->status]);

        return $carousel;
    }

    public function delete(Carousel $carousel, User $actor): void
    {
        $this->ensureCanManage($actor);
        $imagePath = $carousel->gambar;
        $carousel->delete();
        $this->deleteStoredImage($imagePath);
    }

    public function streamImage(string $filename): StreamedResponse
    {
        if ($filename === '' || $filename !== basename($filename)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        $imagePath = "carousel/{$filename}";

        if (! Storage::disk('public')->exists($imagePath)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        return Storage::disk('public')->response($imagePath);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{array<string, mixed>, string|null}
     */
    private function prepareImageData(array $data): array
    {
        $image = $data['gambar'] ?? null;

        if ($image instanceof UploadedFile) {
            $imagePath = $image->store('carousel', 'public');
            $data['gambar'] = $imagePath;

            return [$data, $imagePath];
        }

        unset($data['gambar']);

        return [$data, null];
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function ensureCanManage(User $actor): void
    {
        if (! in_array((int) $actor->role_id, [6, 8], true)) {
            throw new AuthorizationException;
        }
    }
}
