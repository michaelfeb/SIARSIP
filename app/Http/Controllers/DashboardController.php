<?php

namespace App\Http\Controllers;

use App\Models\BerkasSidangNol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $menunggu = BerkasSidangNol::where('status', 1)->count();
        $diterima = BerkasSidangNol::where('status', 2)->count();
        $ditolak  = BerkasSidangNol::where('status', 3)->count();

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // Bar Chart: jumlah berkas dikirim tiap bulan (1 tahun)
        $rawPerMonth = BerkasSidangNol::whereYear('tanggal_dikirim', $year)
            ->where('status', '>=', 1)
            ->selectRaw('MONTH(tanggal_dikirim) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $bulanList = collect(range(1, 12))->map(function ($month) use ($rawPerMonth) {
            return [
                'bulan' => \Carbon\Carbon::create()->month($month)->translatedFormat('F'),
                'total' => $rawPerMonth[$month] ?? 0,
            ];
        });


        // Pie Chart: distribusi program studi dalam bulan tertentu
        $prodiThisMonth = DB::table('berkas_sidang_nol')
            ->join('users', 'berkas_sidang_nol.user_id', '=', 'users.id')
            ->whereYear('berkas_sidang_nol.tanggal_dikirim', $year)
            ->whereMonth('berkas_sidang_nol.tanggal_dikirim', $month)
            ->where('berkas_sidang_nol.status', '>=', 1)
            ->select('users.program_studi', DB::raw('COUNT(*) as total'))
            ->groupBy('users.program_studi')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $this->programStudiLabel((int) $item->program_studi),
                    'total' => $item->total,
                ];
            });


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
