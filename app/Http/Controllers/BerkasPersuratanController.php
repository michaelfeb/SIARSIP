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
            });

        if ($user->role_id === 2) {
            $query->orderByRaw("
                    CASE 
                        WHEN FLOOR(status / 10) = 2 THEN 0
                        WHEN FLOOR(status / 10) = 7 THEN 1
                        ELSE 2
                    END ASC,
                    status ASC,
                    tanggal_dikirim DESC
                ");
        } elseif ($user->role_id === 7) {
            $query->orderByRaw("
                    CASE 
                        WHEN FLOOR(status / 10) = 8 THEN 0
                        ELSE 1
                    END ASC,
                    status ASC,
                    tanggal_dikirim DESC
                ");
        } elseif (in_array($user->role_id, [3, 4, 5, 6])) {
            $query->orderByRaw("
                    CASE 
                        WHEN FLOOR(status / 10) = ? THEN 0
                        ELSE 1
                    END ASC,
                    status ASC,
                    tanggal_dikirim DESC
                ", [$user->role_id]);
        } else {
            $query->orderBy('status')->orderByDesc('tanggal_dikirim');
        }

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

        foreach ($newFiles as $file) {
            if (is_string($file)) {
                $uploadedFiles[] = $file;
            } else {
                $path = $file->store('berkas_mahasiswa', 'secure');
                $uploadedFiles[] = $path;
            }
        }

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


        if ($berkas->status === 11) {
            $berkas->update(['status' => 21]);

            return response()->json([
                'message' => 'Surat berhasil dikirim.',
                'status' => $berkas->status,
            ]);
        }

        return response()->json([
            'message' => 'Surat tidak dapat dikirim karena status tidak valid.',
            'current_status' => $berkas->status,
        ], 400);
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
        $berkas = BerkasPersuratan::findOrFail($id);
        $statusBaru = (int) $request->input('status');

        $rules = [
            'status' => 'required|integer',
            'note' => 'nullable|string',
            'berkas_tambahan.*' => 'nullable|file|mimes:pdf|max:512',
            'berkas_balasan' => 'nullable|array',
            'berkas_balasan.*' => 'file|mimes:doc,docx,pdf|max:512',

        ];

        $messages = [
            'berkas_tambahan.required' => 'Berkas disposisi wajib diisi.',
            'berkas_balasan.required' => 'Surat balasan wajib diisi.',
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.string' => 'Nomor surat harus berupa teks.',
            'berkas_balasan.*.file' => 'Setiap berkas balasan harus berupa file.',
            'berkas_balasan.*.mimes' => 'Setiap berkas balasan harus berformat DOC atau DOCX.',
            'berkas_balasan.*.max' => 'Ukuran maksimal setiap berkas balasan adalah 512kb.',
        ];

        if ($statusBaru === 61) {
            $rules['berkas_tambahan'] = 'required';
        }

        if ($statusBaru === 71) {
            $rules['nomor_surat'] = 'required|string';
            $rules['berkas_balasan'] = 'required|array';
        }

        $validated = $request->validate($rules, $messages);

        // Tambahkan secara manual field hasil upload ke $validated:
        if ($request->hasFile('berkas_tambahan')) {
            if ($berkas->berkas_tambahan) {
                $oldFiles = json_decode($berkas->berkas_tambahan, true);
                foreach ($oldFiles as $oldPath) {
                    Storage::disk('secure')->delete($oldPath);
                }
            }

            $paths = [];
            foreach ($request->file('berkas_tambahan') as $file) {
                $paths[] = $file->store('berkas_tambahan', 'secure');
            }

            $validated['berkas_tambahan'] = json_encode($paths);
        }

        if ($request->hasFile('berkas_balasan')) {
            if ($berkas->berkas_balasan) {
                $oldFiles = json_decode($berkas->berkas_balasan, true);
                foreach ($oldFiles as $oldPath) {
                    Storage::disk('secure')->delete($oldPath);
                }
            }

            $paths = [];
            foreach ($request->file('berkas_balasan') as $file) {
                $paths[] = $file->store('berkas_balasan', 'secure');
            }

            $validated['berkas_balasan'] = json_encode($paths);
        }

        $berkas->fill($validated);
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

    // Filter hanya file PDF
    $pdfFiles = array_filter($balasanFiles, function ($filePath) {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf';
    });

    // Jika hanya satu PDF, langsung download
    if (count($pdfFiles) === 1) {
        $filePath = reset($pdfFiles); // ambil elemen pertama
        $fullPath = storage_path('app/secure_storage/' . $filePath);

        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan di penyimpanan.');
        }

        return response()->download($fullPath);
    }

    // Jika banyak PDF, buat ZIP
    $zipFileName = 'surat_balasan_' . now()->timestamp . '.zip';
    $zipDirectory = storage_path('app/public/temp');
    $zipPath = $zipDirectory . "/$zipFileName";

    if (!file_exists($zipDirectory)) {
        mkdir($zipDirectory, 0755, true);
    }

    $zip = new \ZipArchive;
    if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
        foreach ($pdfFiles as $filePath) {
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
