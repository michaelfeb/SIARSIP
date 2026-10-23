<?php

namespace App\Http\Controllers;

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

        $query = TemplateSurat::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%");
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

        $query = TemplateSurat::query()
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
        $templateSurat = TemplateSurat::findOrFail($id);

        return Inertia::render('TemplateSurat/Show', [
            'templateSurat' => $templateSurat,
        ]);
    }

    public function create()
    {
        return Inertia::render('TemplateSurat/Form', props: [
            'mode' => 'create',
        ]);
    }

    public function edit($id)
    {
        $templateSurat = TemplateSurat::findOrFail($id);

        return Inertia::render('TemplateSurat/Form', [
            'templateSurat' => $templateSurat,
            'mode' => 'edit',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|boolean',
            'tanggal_publish' => 'required|date',
            'dokumen_path' => ($id ? 'nullable' : 'required') . '|file|mimes:doc,docx|max:2048',
        ];

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal terdiri dari 255 karakter.',

            'deskripsi.string' => 'Deskripsi harus berupa teks.',

            'status.required' => 'Status wajib diisi.',
            'status.boolean' => 'Status harus berupa nilai boolean.',

            'tanggal_publish.required' => 'Tanggal publikasi wajib diisi.',
            'tanggal_publish.date' => 'Tanggal publikasi harus berupa tanggal yang valid.',

            'dokumen_path.required' => 'Dokumen template surat wajib diunggah.',
            'dokumen_path.file' => 'Dokumen harus berupa file.',
            'dokumen_path.mimes' => 'Dokumen hanya boleh berformat DOC atau DOCX.',
            'dokumen_path.max' => 'Ukuran maksimal dokumen adalah 2MB.',
        ];


        $validated = $request->validate($rules, $messages);

        if ($request->hasFile('dokumen_path')) {
            $file = $request->file('dokumen_path');
            $path = $file->store('template_surat', 'public');
            $validated['dokumen_path'] = $path;
        } else {
            unset($validated['dokumen_path']);
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
