<?php

namespace App\Http\Controllers;

use App\Models\BerkasSidangNol;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BerkasSidangNolController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $user = auth()->user();

        $query = BerkasSidangNol::with('user')
            ->where(function ($q) use ($user) {
                if ($user->role_id === 1) {
                    // Mahasiswa hanya bisa lihat datanya sendiri
                    $q->where('user_id', $user->id);
                } else {
                    // Sub akademik hanya bisa lihat data status >= 1 (bukan draft)
                    $q->where('status', '>=', 1);
                }
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('nim', 'like', "%{$search}%");
                    })
                        ->orWhere('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("
            CASE 
                WHEN status = 1 THEN 0
                ELSE 1
            END ASC,
            tanggal_dikirim DESC
        ");

        $berkasSidangNol = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($berkasSidangNol);
        }

        return Inertia::render('BerkasSidangNol/Index', [
            'berkas' => $berkasSidangNol,
        ]);
    }


    public function create()
    {
        return Inertia::render('BerkasSidangNol/Form', [
            'mode' => 'create',
        ]);
    }

    public function show($id)
    {
        $berkasSidangNol = BerkasSidangNol::with(['user', 'notes.user'])->findOrFail($id);

        return Inertia::render('BerkasSidangNol/Show', [
            'berkasSidangNol' => $berkasSidangNol,
        ]);
    }

    public function edit($id)
    {
        $berkasSidangNol = BerkasSidangNol::with(['user', 'notes.user'])->findOrFail($id);

        return Inertia::render('BerkasSidangNol/Form', [
            'berkasSidangNol' => $berkasSidangNol,
            'mode' => 'edit',
        ]);
    }

    public function ajuan($id)
    {
        $berkasSidangNol = BerkasSidangNol::with(['user'])->findOrFail($id);
        $nomorSuratTerakhir = BerkasSidangNol::whereNotNull('nomor_surat')->max('nomor_surat');

        if ($nomorSuratTerakhir && str_contains($nomorSuratTerakhir, 'SN-')) {
            $parts = explode('/', $nomorSuratTerakhir);
            $numberPart = $parts[0] ?? '';
            $nomorSuratTerakhir = (int) str_replace('SN-', '', $numberPart);
            $nomorSuratTerakhir++;
        }

        return Inertia::render('BerkasSidangNol/Ajuan', [
            'berkasSidangNol' => $berkasSidangNol,
            'nomorSuratTerakhir' => $nomorSuratTerakhir ?? 1,
        ]);
    }

    public function reset($id)
    {
        $berkas = BerkasSidangNol::findOrFail($id);
        if ($berkas->status === 3) {
            $berkas->status = 0;
            $berkas->save();
        }
        return response()->json(['message' => 'Surat berhasil direset.']);
    }

    public function destroy($id)
    {
        $berkasSidangNol = \App\Models\BerkasSidangNol::with('user')->findOrFail($id);
        if ($berkasSidangNol->status === 2) {
            return redirect()->back()->with('error', 'Berkas yang sudah selesai tidak dapat dihapus.');
        }
        $folderPath = "berkas_sidang_nol/{$berkasSidangNol->user->nim}_{$berkasSidangNol->id}";
        \Illuminate\Support\Facades\Storage::disk('secure')->deleteDirectory($folderPath);
        $berkasSidangNol->delete();

        return redirect()->route('berkas-sidang-nol.index')->with('success', 'Berkas berhasil dihapus.');
    }


    public function save(Request $request, $id = null)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'nomor_surat' => 'nullable|string|max:255',
            'tanggal_dikirim' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'dokumen_hasil_studi' => 'required|file|mimes:pdf|max:1024',
            'dokumen_data_diri' => 'required|file|mimes:pdf|max:1024',
            'dokumen_pddikti_ukt' => 'required|file|mimes:pdf|max:1024',
            'dokumen_ruangbaca_laboratorium_pkkmb_skpi' => 'required|file|mimes:pdf|max:1024',
            'dokumen_office_toefl' => 'required|file|mimes:pdf|max:1024',
            'dokumen_tambahan' => 'nullable|file|mimes:pdf|max:1024',
            'status' => 'required|integer|in:0,1,2,3',
        ];

        $messages = [
            'user_id.required' => 'Mahasiswa wajib diisi.',
            'user_id.exists' => 'Mahasiswa tidak ditemukan.',

            'nomor_surat.string' => 'Nomor surat harus berupa teks.',
            'nomor_surat.max' => 'Nomor surat tidak boleh lebih dari 255 karakter.',

            'tanggal_dikirim.date' => 'Format tanggal dikirim tidak valid.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',

            'dokumen_hasil_studi.required' => 'Dokumen hasil studi wajib diunggah.',
            'dokumen_hasil_studi.file' => 'Dokumen hasil studi harus berupa file.',
            'dokumen_hasil_studi.mimes' => 'Dokumen hasil studi hanya boleh dalam format PDF.',
            'dokumen_hasil_studi.max' => 'Ukuran dokumen hasil studi maksimal 1MB.',

            'dokumen_data_diri.required' => 'Dokumen data diri wajib diunggah.',
            'dokumen_data_diri.file' => 'Dokumen data diri harus berupa file.',
            'dokumen_data_diri.mimes' => 'Dokumen data diri hanya boleh dalam format PDF.',
            'dokumen_data_diri.max' => 'Ukuran dokumen data diri maksimal 1MB.',

            'dokumen_pddikti_ukt.required' => 'Dokumen PDDIKTI & UKT wajib diunggah.',
            'dokumen_pddikti_ukt.file' => 'Dokumen PDDIKTI & UKT harus berupa file.',
            'dokumen_pddikti_ukt.mimes' => 'Dokumen PDDIKTI & UKT hanya boleh dalam format PDF.',
            'dokumen_pddikti_ukt.max' => 'Ukuran dokumen PDDIKTI & UKT maksimal 1MB.',

            'dokumen_ruangbaca_laboratorium_pkkmb_skpi.required' => 'Dokumen bebas ruang baca, laboratorium, PKKMB, dan SKPI wajib diunggah.',
            'dokumen_ruangbaca_laboratorium_pkkmb_skpi.file' => 'Dokumen tersebut harus berupa file.',
            'dokumen_ruangbaca_laboratorium_pkkmb_skpi.mimes' => 'Dokumen tersebut hanya boleh dalam format PDF.',
            'dokumen_ruangbaca_laboratorium_pkkmb_skpi.max' => 'Ukuran dokumen tersebut maksimal 1MB.',

            'dokumen_office_toefl.required' => 'Dokumen Office dan TOEFL wajib diunggah.',
            'dokumen_office_toefl.file' => 'Dokumen Office dan TOEFL harus berupa file.',
            'dokumen_office_toefl.mimes' => 'Dokumen Office dan TOEFL hanya boleh dalam format PDF.',
            'dokumen_office_toefl.max' => 'Ukuran dokumen Office dan TOEFL maksimal 1MB.',

            'dokumen_tambahan.file' => 'Dokumen tambahan harus berupa file.',
            'dokumen_tambahan.mimes' => 'Dokumen tambahan hanya boleh dalam format PDF.',
            'dokumen_tambahan.max' => 'Ukuran dokumen tambahan maksimal 1MB.',

            'status.required' => 'Status wajib diisi.',
            'status.integer' => 'Status harus berupa angka.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];


        if (empty($validated['tanggal_selesai'])) {
            $validated['tanggal_selesai'] = null;
        }

        $validated = $request->validate($rules, $messages);

        $user = User::findOrFail($request->user_id);

        if ($id) {
            $berkas = BerkasSidangNol::findOrFail($id);
        } else {
            $berkas = new BerkasSidangNol();
            $berkas->user_id = $request->user_id;
        }

        $idOrTemp = $berkas->id ?? 'temp';
        $folderPath = "berkas_sidang_nol/{$user->nim}_{$idOrTemp}";

        if (!$id && auth()->user()->role_id !== 1) {
            $validated['status'] = 1;
        }

        if (!$id) {
            $berkas->save();
            $folderPath = "berkas_sidang_nol/{$user->nim}_{$berkas->id}";
        }

        if ($request->hasFile('dokumen_hasil_studi')) {
            $validated['dokumen_hasil_studi'] = $request->file('dokumen_hasil_studi')->storeAs($folderPath, 'dokumen_hasil_studi.pdf', 'secure');
        }

        if ($request->hasFile('dokumen_data_diri')) {
            $validated['dokumen_data_diri'] = $request->file('dokumen_data_diri')->storeAs($folderPath, 'dokumen_data_diri.pdf', 'secure');
        }

        if ($request->hasFile('dokumen_pddikti_ukt')) {
            $validated['dokumen_pddikti_ukt'] = $request->file('dokumen_pddikti_ukt')->storeAs($folderPath, 'dokumen_pddikti_ukt.pdf', 'secure');
        }

        if ($request->hasFile('dokumen_ruangbaca_laboratorium_pkkmb_skpi')) {
            $validated['dokumen_ruangbaca_laboratorium_pkkmb_skpi'] = $request->file('dokumen_ruangbaca_laboratorium_pkkmb_skpi')->storeAs($folderPath, 'dokumen_ruangbaca_laboratorium_pkkmb_skpi.pdf', 'secure');
        }

        if ($request->hasFile('dokumen_office_toefl')) {
            $validated['dokumen_office_toefl'] = $request->file('dokumen_office_toefl')->storeAs($folderPath, 'dokumen_office_toefl.pdf', 'secure');
        }

        if ($request->hasFile('dokumen_tambahan')) {
            $validated['dokumen_tambahan'] = $request->file('dokumen_tambahan')->storeAs($folderPath, 'dokumen_tambahan.pdf', 'secure');
        }

        $berkas->update($validated);

        return redirect()->route('berkas-sidang-nol.index')->with('success', 'Berhasil menyimpan berkas sidang nol.');
    }

    public function getUploads($id)
    {
        $berkas = BerkasSidangNol::findOrFail($id);

        $baseRoute = route('berkas-sidang-nol.download-upload');

        $files = [
            'dokumen_hasil_studi' => $berkas->dokumen_hasil_studi ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_hasil_studi}" : null,
            'dokumen_data_diri' => $berkas->dokumen_data_diri ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_data_diri}" : null,
            'dokumen_pddikti_ukt' => $berkas->dokumen_pddikti_ukt ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_pddikti_ukt}" : null,
            'dokumen_ruangbaca_laboratorium_pkkmb_skpi' => $berkas->dokumen_ruangbaca_laboratorium_pkkmb_skpi ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_ruangbaca_laboratorium_pkkmb_skpi}" : null,
            'dokumen_office_toefl' => $berkas->dokumen_office_toefl ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_office_toefl}" : null,
            'dokumen_tambahan' => $berkas->dokumen_tambahan ? "{$baseRoute}?path=secure_storage/{$berkas->dokumen_tambahan}" : null,
        ];

        return response()->json($files);
    }

    public function downloadUpload(Request $request)
    {
        $path = $request->query('path');

        $fullPath = storage_path('app/' . $path);

        if (!$path || !file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }

    public function keputusan(Request $request, $id)
    {
        $tahun = now()->year;

        if ($request->filled('nomor_surat')) {
            $formatted = 'SN-' . $request->input('nomor_surat') . '/UN8.1.28/DV.01/' . $tahun;
            $request->merge(['nomor_surat' => $formatted]);
        }

        $validated = $request->validate([
            'status' => 'required|numeric|in:2,3',
            'nomor_surat' => [
                'required_if:status,2',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->status == 2 && !is_string($value)) {
                        $fail('Nomor surat harus berupa text.');
                    }
                },
            ],
            'pegawai' => [
                'required_if:status,2',
                function ($attribute, $value, $fail) {
                    if ($value !== null && !in_array((int)$value, [1, 2, 3, 4, 5, 6])) {
                        $fail('Pegawai yang dipilih tidak valid.');
                    }
                },
            ],
            'note' => 'nullable|string|max:1000',
        ]);

        $berkas = BerkasSidangNol::with('user')->findOrFail($id);

        $data = [
            'status' => $validated['status'],
            'tanggal_selesai' => now(),
        ];

        if ($validated['status'] == 2) {
            $data['nomor_surat'] = $validated['nomor_surat'];

            $map = $this->penandatanganMap();
            $pegawaiId = (int) $validated['pegawai'];

            if (!isset($map[$pegawaiId])) {
                return back()->withErrors(['pegawai' => 'Pegawai tidak valid.']);
            }

            $pegawai = $map[$pegawaiId];

            $programStudi = app(UserController::class)->programStudi();

            // Generate PDF
            $pdf = Pdf::loadView('pdf.surat_sidang_nol', [
                'nama' => strtoupper($berkas->user->nama),
                'nim' => $berkas->user->nim,
                'prodi' => $programStudi[$berkas->user->program_studi]['label'],
                'nomor_surat' => $validated['nomor_surat'],
                'tanggal' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
                'penandatangan' => $pegawai['nama'],
                'nip' => $pegawai['nip'],
                'ttd_path' => storage_path('app/secure_storage/ttd/' . $pegawai['ttd']),
            ]);

            $folderPath = "berkas_sidang_nol/{$berkas->user->nim}_{$berkas->id}";
            $filename = 'surat_balasan.pdf';

            Storage::disk('secure')->put("$folderPath/$filename", $pdf->output());

            $data['surat_balasan'] = "$folderPath/$filename";
        }

        $berkas->update($data);

        if ($request->filled('note')) {
            Note::create([
                'berkas_id' => $berkas->id,
                'jenis_berkas' => '2',
                'user_id' => auth()->id(),
                'pesan' => $request->note,
            ]);
        }

        return back()->with('success', 'Keputusan berhasil disimpan dan surat dibuat.');
    }

    public function kirim($id)
    {
        $berkas = BerkasSidangNol::findOrFail($id);


        if ($berkas->status === 0) {
            $berkas->update(['status' => 1]);

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

    public function downloadSurat($id)
    {
        $berkas = BerkasSidangNol::findOrFail($id);

        if (!$berkas->surat_balasan || !Storage::disk('secure')->exists($berkas->surat_balasan)) {
            abort(404, 'Surat sidang nol tidak ditemukan.');
        }

        return Storage::disk('secure')->download($berkas->surat_balasan);
    }

    private function penandatanganMap()
    {
        return [
            1 => ['nama' => 'Nurul Lathifah', 'nip' => '197809072001122002', 'ttd' => 'ttd_ibu_ipah.png'],
            2 => ['nama' => 'Razmeirahmini', 'nip' => '197905272000122002', 'ttd' => 'ttd_ibu_mini.png'],
            3 => ['nama' => 'Yuyun Magfirah', 'nip' => '197910142001122002', 'ttd' => 'ttd_ibu_yuyun.png'],
            4 => ['nama' => "Nie'mattul Ridha", 'nip' => '198511052010012017', 'ttd' => 'ttd_ibu_ridha.png'],
            5 => ['nama' => 'Risda Faulina', 'nip' => '198403062014092003', 'ttd' => 'ttd_ibu_risda.png'],
            6 => ['nama' => 'Fitri Subiaktanti', 'nip' => '197211232006042001', 'ttd' => 'ttd_ibu_fitri.png'],
        ];
    }
}
