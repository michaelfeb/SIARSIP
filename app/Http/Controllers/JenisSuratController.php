<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JenisSuratController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = JenisSurat::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%");
            });

        $jenis_surat = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($jenis_surat);
        }

        return Inertia::render('JenisSurat/Index', [
            'jenis_surat' => $jenis_surat,
        ]);
    }

    public function create()
    {
        return Inertia::render('JenisSurat/Form',);
    }

    public function edit($id)
    {
        $jenis_surat = JenisSurat::findOrFail($id);

        return Inertia::render('JenisSurat/Form', [
            'jenis_surat' => $jenis_surat,
            'mode' => 'edit',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'status' => ['required'],
        ];

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'status.required' => 'Status wajib diisi.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($id) {
            $jenis_surat = JenisSurat::findOrFail($id);
            $jenis_surat->update([
                'nama' => $request->nama,
                'status' => $request->status,
            ]);
            return redirect()->route('jenis-surat.index')->with('success', '');
        } else {
            JenisSurat::create([
                'nama' => $request->nama,
                'status' => $request->status,
            ]);
            return redirect()->route('jenis-surat.index')->with('success', '');
        }
    }

    public function destroy($id)
    {
        $jenis_surat = JenisSurat::findOrFail($id);
        $jenis_surat->delete();
    }

    public function toggle($id)
    {
        $jenisSurat = JenisSurat::findOrFail($id);
        $jenisSurat->status = !$jenisSurat->status;
        $jenisSurat->save();

        return response()->json(['success' => true]);
    }
}
