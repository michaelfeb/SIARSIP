<?php

namespace App\Http\Controllers;

use App\Models\BerkasSidangNol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        // SIDANG NOL filter
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // PERSURATAN filter (pisah)
        $persuratanMonth = $request->query('persuratan_month', now()->month);
        $persuratanYear = $request->query('persuratan_year', now()->year);

        // SIDANG NOL data
        $menunggu = BerkasSidangNol::where('status', 1)->count();
        $diterima = BerkasSidangNol::where('status', 2)->count();
        $ditolak  = BerkasSidangNol::where('status', 3)->count();

        $rawPerMonth = BerkasSidangNol::whereYear('tanggal_dikirim', $year)
            ->where('status', '>=', 1)
            ->selectRaw('MONTH(tanggal_dikirim) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $bulanList = collect(range(1, 12))->map(fn($m) => [
            'bulan' => \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F'),
            'total' => $rawPerMonth[$m] ?? 0,
        ]);

        $prodiThisMonth = DB::table('berkas_sidang_nol')
            ->join('users', 'berkas_sidang_nol.user_id', '=', 'users.id')
            ->whereYear('berkas_sidang_nol.tanggal_dikirim', $year)
            ->whereMonth('berkas_sidang_nol.tanggal_dikirim', $month)
            ->where('berkas_sidang_nol.status', '>=', 1)
            ->select('users.program_studi', DB::raw('COUNT(*) as total'))
            ->groupBy('users.program_studi')
            ->get()
            ->map(fn($item) => [
                'label' => $this->programStudiLabel((int) $item->program_studi),
                'total' => $item->total,
            ]);

        // PERSURATAN data
        // 1. Menunggu: status = 21 (baru masuk ke resepsionis)
        $persuratanMasuk = DB::table('berkas_persuratan')
            ->where('status', 21)
            ->count();

        // 2. Diproses: status antara 30 sampai 89
        $persuratanProses = DB::table('berkas_persuratan')
            ->whereBetween('status', [30, 89])
            ->count();

        // 3. Ditolak: semua status yang berakhiran angka 3 (23, 33, 43, ..., 93)
        $persuratanDitolak = DB::table('berkas_persuratan')
            ->whereRaw('MOD(status, 10) = 3')
            ->count();

        $persuratanSelesai = DB::table('berkas_persuratan')->where('status', 91)->count();

        $rawPersuratanPerMonth = DB::table('berkas_persuratan')
            ->whereYear('created_at', $persuratanYear)
            ->where('status', '>=', 10)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

            $bulanPersuratanList = collect(range(1, 12))->map(fn($m) => [
                'bulan' => \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F'),
                'total' => $rawPersuratanPerMonth[$m] ?? 0,
            ]);

        $prodiPersuratanThisMonth = DB::table('berkas_persuratan')
            ->join('users', 'berkas_persuratan.user_id', '=', 'users.id')
            ->whereYear('berkas_persuratan.created_at', $persuratanYear)
            ->whereMonth('berkas_persuratan.created_at', $persuratanMonth)
            ->where('berkas_persuratan.status', '>=', 10)
            ->select('users.program_studi', DB::raw('COUNT(*) as total'))
            ->groupBy('users.program_studi')
            ->get()
            ->map(fn($item) => [
                'label' => $this->programStudiLabel((int) $item->program_studi),
                'total' => $item->total,
            ]);

        return Inertia::render('Dashboard/Index', [
            'summary' => [
                'menunggu' => $menunggu,
                'diterima' => $diterima,
                'ditolak' => $ditolak,
            ],
            'berkasPerMonth' => $bulanList,
            'prodiThisMonth' => $prodiThisMonth,
            'selectedMonth' => (int) $month,
            'selectedYear' => (int) $year,

            'persuratan' => [
                'summary' => [
                    'masuk' => $persuratanMasuk,
                    'proses' => $persuratanProses,
                    'selesai' => $persuratanSelesai,
                    'ditolak' => $persuratanDitolak,
                ],
                'berkasPerMonth' => $bulanPersuratanList,
                'prodiThisMonth' => $prodiPersuratanThisMonth,
            ],
            'persuratanMonth' => (int) $persuratanMonth,
            'persuratanYear' => (int) $persuratanYear,
        ]);
    }

    function programStudiLabel(int $id): string
    {
        return [
            1 => 'Matematika',
            2 => 'Kimia',
            3 => 'Biologi',
            4 => 'Fisika',
            5 => 'Farmasi',
            6 => 'Ilmu Komputer',
            7 => 'Statistika',
            8 => 'Profesi Apoteker',
        ][$id] ?? 'Tidak Diketahui';
    }
}
