<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\LinkLandingPage;
use App\Models\TemplateSurat;
use Illuminate\Container\Attributes\Storage;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    //
    public function index()
    {
        $templateSurat = TemplateSurat::where('status', true)
            ->orderBy('tanggal_publish', 'desc')
            ->get();

        $carousel = Carousel::where('status', true)
            ->orderBy('tanggal_publish', 'desc')
            ->get();

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
