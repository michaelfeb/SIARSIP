<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TemplateSuratController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = TemplateSurat::with('jenisSurat')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhereHas('jenisSurat', function ($jenis) use ($search) {
                            $jenis->where('nama', 'like', "%{$search}%");
                        });
                });
            });

        $templates = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return Inertia::render('TemplateSurat/Index', [
            'templates' => $templates,
        ]);
    }

    public function indexMahasiswa(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = TemplateSurat::with('jenisSurat')
            ->where('status', 1) // hanya ambil yang aktif
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                });
            });

        $templates = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return Inertia::render('TemplateSurat/IndexMahasiswa', [
            'templates' => $templates,
        ]);
    }

    public function show($id)
    {
        $templateSurat = TemplateSurat::with('jenisSurat')->findOrFail($id);

        return Inertia::render('TemplateSurat/Show', [
            'templateSurat' => $templateSurat,
        ]);
    }

    public function create()
    {
        $jenisSurat = JenisSurat::where('status', true)->get();
        return Inertia::render('TemplateSurat/Form', props: [
            'jenisSurat' => $jenisSurat,
            'mode' => 'create',
        ]);
    }

    public function edit($id)
    {
        $templateSurat = TemplateSurat::with('jenisSurat')->findOrFail($id);
        $jenisSurat = JenisSurat::where('status', true)->get();

        return Inertia::render('TemplateSurat/Form', [
            'templateSurat' => $templateSurat,
            'jenisSurat' => $jenisSurat,
            'mode' => 'edit',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
            'status' => 'required|boolean',
            'tanggal_publish' => 'required|date',
            'dokumen_path' => 'required|file|mimes:doc,docx|max:2048',
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('dokumen_path')) {
            $file = $request->file('dokumen_path');
            $path = $file->store('template_surat', 'public');
            $validated['dokumen_path'] = $path;
        }

        if ($id) {
            $template = TemplateSurat::findOrFail($id);
            $template->update($validated);
        } else {
            $template = TemplateSurat::create($validated);
        }

        return redirect()->route('template-surat.index')->with('success', 'Berhasil menyimpan template surat.');
    }

    public function destroy($id)
    {
        $template = TemplateSurat::findOrFail($id);

        if ($template->dokumen_path && Storage::disk('public')->exists($template->dokumen_path)) {
            Storage::disk('public')->delete($template->dokumen_path);
        }

        $template->delete();

        return redirect()->route('template-surat.index')->with('success', 'Template surat berhasil dihapus.');
    }

    public function downloadTemplate($id)
    {
        $template = TemplateSurat::findOrFail($id);

        return Storage::disk('public')->download($template->dokumen_path, $template->nama . '.docx');
    }
}
