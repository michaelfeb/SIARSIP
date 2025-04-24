<?php

namespace App\Http\Controllers;

use App\Models\BerkasPersuratan;
use App\Models\JenisSurat;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BerkasPersuratanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $user = auth()->user();

        $query = BerkasPersuratan::with(['user', 'jenisSurat'])
            ->when($user->role_id === 1, function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($user->role_id === 2, function ($q) {
                $q->where('status', '>=', 21);
            })
            ->when($user->role_id === 3, function ($q) {
                $q->where('status', '>=', 31);
            })
            ->when($user->role_id === 4, function ($q) {
                $q->where('status', '>=', 41);
            })
            ->when($user->role_id === 5, function ($q) {
                $q->where('status', '>=', 51);
            })
            ->when($user->role_id === 6, function ($q) {
                $q->where('status', '>=', 61);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nim', 'like', "%{$search}%")
                            ->orWhere('nama', 'like', "%{$search}%");
                    })
                        ->orWhere('nomor_surat', 'like', "%{$search}%");
                });
            })
            ->orderBy('tanggal_dikirim', 'desc');

        $berkasPersuratan = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($berkasPersuratan);
        }

        return Inertia::render('BerkasPersuratan/Index', [
            'berkas' => $berkasPersuratan,
        ]);
    }


    public function edit($id)
    {
        $berkasPersuratan = BerkasPersuratan::with(['user', 'jenisSurat'])->findOrFail($id);

        return Inertia::render('BerkasPersuratan/Form', [
            'berkasPersuratan' => $berkasPersuratan,
            'jenisSurat' => JenisSurat::all(),
            'mode' => 'edit',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'nomor_surat' => 'nullable|string',
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
            'keterangan' => 'nullable|string',
            'berkas_mahasiswa' => 'required|array',
            'berkas_mahasiswa.*' => 'file|mimes:pdf|max:5120',
            'status' => 'required|integer',
            'tanggal_dikirim' => 'required|date',
        ];

        $validated = $request->validate($rules);

        $berkasLama = [];

        if ($id) {
            $berkas = \App\Models\BerkasPersuratan::findOrFail($id);
            $berkasLama = json_decode($berkas->berkas_mahasiswa, true) ?? [];
        }

        $uploadedFiles = [];
        $newFiles = $request->file('berkas_mahasiswa') ?? [];

        // cek file satu per satu
        foreach ($newFiles as $file) {
            if (is_string($file)) {
                // Kalau file berupa string, berarti file lama, tinggal keep
                $uploadedFiles[] = $file;
            } else {
                // Kalau file berupa file upload baru
                $path = $file->store('berkas_mahasiswa', 'public');
                $uploadedFiles[] = $path;
            }
        }

        $filesToDelete = array_diff($berkasLama, $uploadedFiles);
        foreach ($filesToDelete as $path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        $validated['berkas_mahasiswa'] = json_encode($uploadedFiles);

        if ($id) {
            $berkas->update($validated);
        } else {
            $berkas = \App\Models\BerkasPersuratan::create($validated);
        }

        return redirect()->route('berkas-persuratan.index')->with('success', 'Berhasil menyimpan berkas persuratan.');
    }

    public function create()
    {
        $jenisSurat = JenisSurat::where('status', 1)->get();

        return Inertia::render('BerkasPersuratan/Form', [
            'jenisSurat' => $jenisSurat,
            'mode' => 'create',
        ]);
    }

    public function show($id)
    {
        $berkasPersuratan = BerkasPersuratan::with(['user', 'jenisSurat', 'notes.user'])->findOrFail($id);

        return Inertia::render('BerkasPersuratan/Show', [
            'berkasPersuratan' => $berkasPersuratan,
        ]);
    }

    public function ajuan($id)
    {
        $berkasPersuratan = BerkasPersuratan::with(['user', 'jenisSurat'])->findOrFail($id);

        return Inertia::render('BerkasPersuratan/Show', [
            'berkasPersuratan' => $berkasPersuratan,
            'mode' => 'ajuan'
        ]);
    }

    public function destroy($id)
    {
        $berkas = \App\Models\BerkasPersuratan::findOrFail($id);

        $filesToDelete = json_decode($berkas->berkas_mahasiswa, true) ?? [];
        foreach ($filesToDelete as $path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        $berkas->delete();

        return redirect()->route('berkas-persuratan.index')->with('success', 'Berkas berhasil dihapus.');
    }

    public function kirim($id)
    {
        $berkas = BerkasPersuratan::findOrFail($id);
        if ($berkas->status == 11) {
            $berkas->status = 21;
            $berkas->save();
        }

        return response()->json(['message' => 'Surat berhasil dikirim.']);
    }

    public function reset($id)
    {
        $berkas = BerkasPersuratan::findOrFail($id);
        if ($berkas->status % 10 === 3) {
            $berkas->status = 11;
            $berkas->save();
        }
        return response()->json(['message' => 'Surat berhasil direset.']);
    }

    public function keputusan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $berkas = BerkasPersuratan::findOrFail($id);
        $berkas->status = $request->status;
        $berkas->save();

        if ($request->filled('note')) {
            Note::create([
                'berkas_persuratan_id' => $berkas->id,
                'user_id' => Auth::id(),
                'pesan' => $request->note,
            ]);
        }

        return redirect()->back()->with('success', 'Keputusan berhasil disimpan.');
    }
}
