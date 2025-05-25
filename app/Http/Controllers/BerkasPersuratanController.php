<?php

namespace App\Http\Controllers;

use App\Models\BerkasPersuratan;
use App\Models\JenisSurat;
use App\Models\Note;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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
                        ->orWhere('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            });
        if ($user->role_id === 2) {
            $query->orderByRaw("
                CASE 
                    WHEN status = 21 THEN 0
                    WHEN status = 71 THEN 0
                    WHEN FLOOR(status / 10) = 2 THEN 1
                    WHEN FLOOR(status / 10) = 3 THEN 2
                    WHEN FLOOR(status / 10) = 4 THEN 3
                    WHEN FLOOR(status / 10) = 5 THEN 4
                    WHEN FLOOR(status / 10) = 6 THEN 5
                    WHEN FLOOR(status / 10) = 7 THEN 6
                    WHEN FLOOR(status / 10) = 8 THEN 7
                    WHEN FLOOR(status / 10) = 9 THEN 8
                    ELSE 9
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

    public function edit(BerkasPersuratan $berkasPersuratan)
    {
        $berkasPersuratan->load(['user', 'jenisSurat']);

        return Inertia::render('BerkasPersuratan/Form', [
            'berkasPersuratan' => $berkasPersuratan,
            'jenisSurat' => JenisSurat::all(),
            'mode' => 'edit',
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, BerkasPersuratan $berkasPersuratan)
    {
        $berkasPersuratan->load(['user', 'jenisSurat']);
        return $this->save($request, $berkasPersuratan->id);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'user_id' => ['nullable', 'exists:users,id'],
            'nomor_surat' => 'nullable|string',
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
            'keterangan' => 'required|string',
            'berkas_mahasiswa' => 'required|array',
            'berkas_mahasiswa.*' => 'file|mimes:pdf|max:1024',
            'berkas_balasan' => 'nullable',
            'berkas_balasan.*' => 'file|mimes:pdf|max:1024',
            'berkas_tambahan' => 'nullable',
            'berkas_tambahan.*' => 'file|mimes:pdf|max:1024',
            'status' => 'required|integer',
            'tanggal_dikirim' => 'required|date',
            'program_studi' => 'required|in:1,2,3,4,5,6,7,8',
        ];

        $messages = [
            'user_id.required' => 'Mahasiswa wajib diisi.',
            'user_id.exists' => 'Mahasiswa tidak ditemukan.',
            'nomor_surat.string' => 'Nomor surat harus berupa teks.',
            'jenis_surat_id.required' => 'Jenis surat wajib diisi.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'berkas_mahasiswa.required' => 'Upload berkas wajib diunggah.',
            'berkas_mahasiswa.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_mahasiswa.*.max' => 'Ukuran setiap berkas maksimal 1MB.',
            'berkas_balasan.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_balasan.*.max' => 'Ukuran setiap berkas maksimal 1MB.',
            'berkas_tambahan.*.mimes' => 'Berkas hanya boleh dalam format PDF.',
            'berkas_tambahan.*.max' => 'Ukuran setiap berkas maksimal 1MB.',
            'status.required' => 'Status wajib diisi.',
            'tanggal_dikirim.required' => 'Tanggal dikirim wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
            'program_studi.in' => 'Program studi tidak valid.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $isCreate = !$id;
        $isStaff = auth()->user()->role_id !== 1;
        $userId = $request->input('user_id');
        $keterangan = trim($request->input('keterangan'));

        if ($isCreate && $isStaff) {
            if (empty($userId)) {
                // user_id tidak diisi → keterangan wajib dan harus sesuai format
                if (!$keterangan) {
                    $validator->errors()->add('keterangan', 'Keterangan wajib diisi jika mahasiswa tidak dipilih.');
                } else {
                    // Format harus: n. Nama (NIM)
                    $lines = explode("\n", $keterangan);
                    foreach ($lines as $index => $line) {
                        if (trim($line) === '') continue;
                        if (!preg_match('/^\d+\.\s.+\(\d+\)$/', trim($line))) {
                            $validator->errors()->add('keterangan', "Format baris " . ($index + 1) . " tidak valid. Gunakan format: " . ($index + 1) . ". Nama (NIM)");
                            break;
                        }
                    }
                }
            } else {
                // user_id diisi → user_id harus valid, keterangan bebas
                $rules['user_id'] = ['required', 'exists:users,id'];
            }
        } else {
            if (auth()->user()->role_id === 1) {
                $rules['user_id'] = ['required', 'exists:users,id'];
                $rules['keterangan'] = ['required', 'string'];
            } else {
                $rules['user_id'] = ['nullable', 'exists:users,id'];
                $rules['keterangan'] = ['nullable', 'string'];
            }
        }

        $validator->setRules($rules);
        $validated = $validator->validate();

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

        if (!$id && auth()->user()->role_id !== 1) {
            $validated['status'] = 21;
        }

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


    public function show(BerkasPersuratan $berkasPersuratan)
    {
        $berkasPersuratan->load(['user', 'jenisSurat', 'notes.user']);

        return Inertia::render('BerkasPersuratan/Show', [
            'berkasPersuratan' => $berkasPersuratan,
        ]);
    }

    public function ajuan(BerkasPersuratan $berkasPersuratan)
    {
        $berkasPersuratan->load(['user', 'jenisSurat', 'notes.user']);

        return Inertia::render('BerkasPersuratan/Show', [
            'berkasPersuratan' => $berkasPersuratan,
            'mode' => 'ajuan'
        ]);
    }

    public function destroy(BerkasPersuratan $berkasPersuratan)
    {
        $secureDisk = \Illuminate\Support\Facades\Storage::disk('secure');

        $fieldsToDelete = [
            'berkas_mahasiswa',
            'berkas_balasan',
            'berkas_tambahan',
        ];

        foreach ($fieldsToDelete as $field) {
            $files = json_decode($berkasPersuratan->{$field}, true) ?? [];
            foreach ($files as $path) {
                $secureDisk->delete($path);
            }
        }

        $berkasPersuratan->delete();

        return redirect()->route('berkas-persuratan.index')->with('success', 'Berkas berhasil dihapus.');
    }


    public function kirim(BerkasPersuratan $berkasPersuratan)
    {
        $berkasPersuratan->load(['user', 'jenisSurat', 'notes.user']);

        if ($berkasPersuratan->status === 11) {
            $berkasPersuratan->update(['status' => 21]);

            return response()->json([
                'message' => 'Surat berhasil dikirim.',
                'status' => $berkasPersuratan->status,
            ]);
        }

        return response()->json([
            'message' => 'Surat tidak dapat dikirim karena status tidak valid.',
            'current_status' => $berkasPersuratan->status,
        ], 400);
    }


    public function reset(BerkasPersuratan $berkasPersuratan)
    {
        if ($berkasPersuratan->status % 10 === 3) {
            $berkasPersuratan->status = 11;
            $berkasPersuratan->save();
        }
        return response()->json(['message' => 'Surat berhasil direset.']);
    }


    public function keputusan(Request $request, BerkasPersuratan $berkasPersuratan)
    {
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

        if ($request->hasFile('berkas_tambahan')) {
            if ($berkasPersuratan->berkas_tambahan) {
                $oldFiles = json_decode($berkasPersuratan->berkas_tambahan, true);
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
            if ($berkasPersuratan->berkas_balasan) {
                $oldFiles = json_decode($berkasPersuratan->berkas_balasan, true);
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

        $berkasPersuratan->fill($validated);
        $berkasPersuratan->save();

        if ($request->filled('note')) {
            Note::create([
                'berkas_id' => $berkasPersuratan->id,
                'jenis_berkas' => '1',
                'user_id' => Auth::id(),
                'pesan' => $request->note,
            ]);
        }

        return redirect()->back()->with('success', 'Keputusan berhasil disimpan.');
    }


    public function downloadBalasan(BerkasPersuratan $berkasPersuratan)
    {
        if (!$berkasPersuratan->berkas_balasan) {
            abort(404, 'Berkas balasan tidak ditemukan.');
        }

        $balasanFiles = json_decode($berkasPersuratan->berkas_balasan, true);

        $pdfFiles = array_filter($balasanFiles, function ($filePath) {
            return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf';
        });

        if (count($pdfFiles) === 1) {
            $filePath = reset($pdfFiles);
            $fullPath = storage_path('app/secure_storage/' . $filePath);

            if (!file_exists($fullPath)) {
                abort(404, 'File tidak ditemukan di penyimpanan.');
            }

            return response()->download($fullPath);
        }

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
