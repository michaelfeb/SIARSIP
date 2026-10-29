<?php

namespace App\Http\Controllers;

use App\Models\LinkLandingPage;
use App\Models\TemplateSurat;
use App\Services\CarouselService;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    //
    public function index(CarouselService $carouselService)
    {
        $templateSurat = TemplateSurat::where('status', true)
            ->orderBy('tanggal_publish', 'desc')
            ->get();

        $carousel = $carouselService->publishedForLanding();

        $linkLandingPage = LinkLandingPage::where('status', true)
            ->orderBy('no_urut')
            ->get();

        return Inertia::render('LandingPage/Index', [
            'templateSurat' => $templateSurat,
            'carousel' => $carousel,
            'linkLandingPage' => $linkLandingPage,
        ]);
    }
}
