<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfGeneratorService
{
    /**
     * Render a letter's configured Blade view into PDF bytes.
     *
     * @param  array<string, mixed>  $data
     */
    public function output(string $view, array $data, string $paper = 'a4', string $orientation = 'portrait'): string
    {
        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->output();
    }
}
