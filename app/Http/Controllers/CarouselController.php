<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarouselRequest;
use App\Models\Carousel;
use App\Services\CarouselService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CarouselController extends Controller
{
    public function index(CarouselRequest $request, CarouselService $carouselService)
    {
        $filters = $request->filters();
        $carousel = $carouselService->datatable($filters);

        if ($request->wantsJson()) {
            return response()->json($carousel);
        }

        return Inertia::render('Carousel/Index', compact('carousel', 'filters'));
    }

    public function create()
    {
        return Inertia::render('Carousel/Form', ['mode' => 'create']);
    }

    public function store(CarouselRequest $request, CarouselService $carouselService)
    {
        $carouselService->create($request->user(), $request->validated());

        return redirect()->route('carousel.index')->with('success', 'Carousel berhasil ditambahkan.');
    }

    public function edit(Carousel $carousel)
    {
        return Inertia::render('Carousel/Form', ['carousel' => $carousel, 'mode' => 'edit']);
    }

    public function update(CarouselRequest $request, Carousel $carousel, CarouselService $carouselService)
    {
        $carouselService->update($carousel, $request->user(), $request->validated());

        return redirect()->route('carousel.index')->with('success', 'Carousel berhasil diperbarui.');
    }

    public function toggle(Request $request, Carousel $carousel, CarouselService $carouselService)
    {
        $carouselService->toggleStatus($carousel, $request->user());

        return redirect()->route('carousel.index')->with('success', 'Status carousel berhasil diperbarui.');
    }

    public function destroy(Request $request, Carousel $carousel, CarouselService $carouselService)
    {
        $carouselService->delete($carousel, $request->user());

        return redirect()->route('carousel.index')->with('success', 'Carousel berhasil dihapus.');
    }

    public function showImage(string $filename, CarouselService $carouselService): StreamedResponse
    {
        return $carouselService->streamImage($filename);
    }
}
