<?php

namespace App\Http\Controllers;

use App\Models\BerkasSidangNol;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->role_id === 1) {
            return redirect()->route('berkas-sidang-nol.index');
        }

        Carbon::setLocale('id');

        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $menunggu = BerkasSidangNol::query()
            ->where('status', 1)
            ->whereYear('tanggal_dikirim', $year)
            ->whereMonth('tanggal_dikirim', $month)
            ->count();

        $diterima = BerkasSidangNol::query()
            ->where('status', 2)
            ->whereYear('tanggal_dikirim', $year)
            ->whereMonth('tanggal_dikirim', $month)
            ->count();

        $ditolak = BerkasSidangNol::query()
            ->where('status', 3)
            ->whereYear('tanggal_dikirim', $year)
            ->whereMonth('tanggal_dikirim', $month)
            ->count();

        $monthExpression = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', tanggal_dikirim) AS INTEGER)"
            : 'MONTH(tanggal_dikirim)';

        $rawPerMonth = BerkasSidangNol::query()
            ->whereYear('tanggal_dikirim', $year)
            ->where('status', '>=', 1)
            ->selectRaw("{$monthExpression} as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        $berkasPerMonth = collect(range(1, 12))->map(fn (int $currentMonth) => [
            'bulan' => Carbon::create()->month($currentMonth)->translatedFormat('F'),
            'total' => $rawPerMonth[$currentMonth] ?? 0,
        ]);

        $prodiThisMonth = DB::table('berkas_sidang_nol')
            ->join('users', 'berkas_sidang_nol.user_id', '=', 'users.id')
            ->whereYear('berkas_sidang_nol.tanggal_dikirim', $year)
            ->whereMonth('berkas_sidang_nol.tanggal_dikirim', $month)
            ->where('berkas_sidang_nol.status', '>=', 1)
            ->select('users.program_studi', DB::raw('COUNT(*) as total'))
            ->groupBy('users.program_studi')
            ->get()
            ->map(fn ($item) => [
                'label' => $this->programStudiLabel((int) $item->program_studi),
                'total' => $item->total,
            ]);

        return Inertia::render('Dashboard/index', [
            'summary' => [
                'menunggu' => $menunggu,
                'diterima' => $diterima,
                'ditolak' => $ditolak,
            ],
            'berkasPerMonth' => $berkasPerMonth,
            'prodiThisMonth' => $prodiThisMonth,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    private function programStudiLabel(int $id): string
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
