<?php

namespace App\Http\Controllers;

use App\Http\Requests\TemplateSuratRequest;
use App\Models\TemplateSurat;
use App\Services\TemplateSuratService;
use Inertia\Inertia;

class TemplateSuratController extends Controller
{
    public function index(TemplateSuratRequest $request, TemplateSuratService $templateSuratService)
    {
        $filters = $request->filters();
        $templates = $templateSuratService->paginate($filters);
        if ($request->wantsJson()) return response()->json($templates);
        return Inertia::render('TemplateSurat/Index', compact('templates', 'filters'));
    }

    public function indexMahasiswa(TemplateSuratRequest $request, TemplateSuratService $templateSuratService)
    {
        $filters = $request->filters();
        $templates = $templateSuratService->paginate($filters, true);
        if ($request->wantsJson()) return response()->json($templates);
        return Inertia::render('TemplateSurat/IndexMahasiswa', compact('templates', 'filters'));
    }

    public function show(TemplateSurat $templateSurat)
    {
        return Inertia::render('TemplateSurat/Show', compact('templateSurat'));
    }
    public function create()
    {
        return Inertia::render('TemplateSurat/Form', ['mode' => 'create']);
    }
    public function edit(TemplateSurat $templateSurat)
    {
        return Inertia::render('TemplateSurat/Form', ['templateSurat' => $templateSurat, 'mode' => 'edit']);
    }

    public function store(TemplateSuratRequest $request, TemplateSuratService $templateSuratService)
    {
        $templateSuratService->save($request->validated());
        return redirect()->route('template-surat.index')->with('success', 'Berhasil menyimpan template surat.');
    }

    public function update(TemplateSuratRequest $request, TemplateSurat $templateSurat, TemplateSuratService $templateSuratService)
    {
        $templateSuratService->save($request->validated(), $templateSurat);
        return redirect()->route('template-surat.index')->with('success', 'Berhasil menyimpan template surat.');
    }

    public function destroy(TemplateSurat $templateSurat, TemplateSuratService $templateSuratService)
    {
        $templateSuratService->delete($templateSurat);
        return redirect()->route('template-surat.index')->with('success', 'Template surat berhasil dihapus.');
    }

    public function downloadTemplate(TemplateSurat $templateSurat, TemplateSuratService $templateSuratService)
    {
        return $templateSuratService->download($templateSurat);
    }
}
