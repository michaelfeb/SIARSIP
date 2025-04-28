<?php

namespace App\Http\Controllers;

use App\Models\BerkasPersuratan;
use App\Models\JenisSurat;
use App\Models\Note;
use Illuminate\Support\Facades\Storage;
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

        $roleId = $user->role_id;

        $query = BerkasPersuratan::with(['user', 'jenisSurat'])
            ->where(function ($q) use ($user) {
                if ($user->role_id === 1) {
                    $q->where('user_id', $user->id);
                } else {
                    $q->where('status', '>=', 12);
                }
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
            ->orderByRaw("
            CASE 
                WHEN FLOOR(status / 10) = ? THEN 0 
                ELSE 1 
            END, 
            CASE 
                WHEN FLOOR(status / 10) = ? THEN status
                ELSE status
            END ASC,
            tanggal_dikirim DESC
        ", [$user->role_id, $user->role_id]);

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
            'berkas_balasan' => 'nullable',
            'berkas_balasan.*' => 'file|mimes:pdf|max:5120',
            'berkas_tambahan' => 'nullable',
            'berkas_tambahan.*' => 'file|mimes:pdf|max:5120',
            'status' => 'required|integer',
            'tanggal_dikirim' => 'required|date',
        ];

        $messages = [
            'user_id.required' => 'Mahasiswa wajib diisi.',
            'user_id.exists' => 'Mahasiswa tidak ditemukan.',
            'nomor_surat.string' => 'Nomor surat harus berupa teks.',
            'jenis_surat_id.required' => 'Jenis surat wajib diisi.',
            'berkas_mahasiswa.required' => 'Upload berkas wajib diunggah.',
            'berkas_mahasiswa.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_mahasiswa.*.max' => 'Ukuran setiap berkas maksimal 5MB.',
            'berkas_balasan.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_balasan.*.max' => 'Ukuran setiap berkas maksimal 5MB.',
            'berkas_tambahan.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_tambahan.*.max' => 'Ukuran setiap berkas maksimal 5MB.',
            'status.required' => 'Status wajib diisi.',
            'tanggal_dikirim.required' => 'Tanggal dikirim wajib diisi.',
        ];

        $validated = $request->validate($rules, $messages);

        $berkasLama = [];
        $replyFilesLama = [];
        $berkasTambahanLama = [];

        if ($id) {
            $berkas = \App\Models\BerkasPersuratan::findOrFail($id);
            $berkasLama = json_decode($berkas->berkas_mahasiswa, true) ?? [];
            $replyFilesLama = json_decode($berkas->berkas_balasan, true) ?? [];
            $berkasTambahanLama = json_decode($berkas->berkas_tambahan, true) ?? [];
        }

        $uploadedFiles = [];
        $uploadedReplyFiles = [];
        $uploadedAdditionalFiles = [];
        $newFiles = $request->file('berkas_mahasiswa') ?? [];
        $newReplyFiles = $request->file('berkas_balasan') ?? [];
        $newAdditionalFiles = $request->file('berkas_tambahan') ?? [];

        // Upload berkas mahasiswa ke secure_storage/berkas_mahasiswa/
        foreach ($newFiles as $file) {
            if (is_string($file)) {
                $uploadedFiles[] = $file;
            } else {
                $path = $file->store('berkas_mahasiswa', 'secure');
                $uploadedFiles[] = $path;
            }
        }

        // Upload berkas balasan ke secure_storage/berkas_balasan/
        foreach ($newReplyFiles as $file) {
            if (is_string($file)) {
                $uploadedReplyFiles[] = $file;
            } else {
                $path = $file->store('berkas_balasan', 'secure');
                $uploadedReplyFiles[] = $path;
            }
        }

        // Upload berkas tambahan ke secure_storage/berkas_tambahan/
        foreach ($newAdditionalFiles as $file) {
            if (is_string($file)) {
                $uploadedAdditionalFiles[] = $file;
            } else {
                $path = $file->store('berkas_tambahan', 'secure');
                $uploadedAdditionalFiles[] = $path;
            }
        }

        // Hapus berkas lama kalau ada
        foreach (array_diff($berkasLama, $uploadedFiles) as $path) {
            Storage::disk('secure')->delete($path);
        }
        foreach (array_diff($replyFilesLama, $uploadedReplyFiles) as $path) {
            Storage::disk('secure')->delete($path);
        }
        foreach (array_diff($berkasTambahanLama, $uploadedAdditionalFiles) as $path) {
            Storage::disk('secure')->delete($path);
        }

        $validated['berkas_mahasiswa'] = json_encode($uploadedFiles);
        $validated['berkas_balasan'] = json_encode($uploadedReplyFiles);
        $validated['berkas_tambahan'] = json_encode($uploadedAdditionalFiles);

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
        $berkasPersuratan = BerkasPersuratan::with(['user', 'jenisSurat', 'notes.user'])->findOrFail($id);

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

    public function downloadBalasan($id)
    {
        $berkas = BerkasPersuratan::findOrFail($id);

        if (!$berkas->berkas_balasan) {
            abort(404, 'Berkas balasan tidak ditemukan.');
        }

        $balasanFiles = json_decode($berkas->berkas_balasan, true);

        // Jika hanya satu file, langsung download
        if (count($balasanFiles) === 1) {
            return Storage::disk('secure_storage')->download($balasanFiles[0]);
        }

        $zipFileName = 'surat_balasan_' . now()->timestamp . '.zip';
        $zipPath = storage_path("app/public/temp/$zipFileName");

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($balasanFiles as $filePath) {
                $fullPath = storage_path('app/secure_storage/' . $filePath);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, basename($filePath));
                }
            }
            $zip->close();
        } else {
            abort(500, 'Gagal membuat file zip.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function downloadUpload($filename)
    {
        $path = 'secure_storage/' . $filename;

        return response()->download(storage_path('app/' . $path));
    }
}
