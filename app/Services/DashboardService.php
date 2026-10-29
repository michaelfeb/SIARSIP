<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /** @var array<int, string> */
    private const PROGRAM_STUDI = [
        1 => 'Matematika',
        2 => 'Kimia',
        3 => 'Biologi',
        4 => 'Fisika',
        5 => 'Farmasi',
        6 => 'Ilmu Komputer',
        7 => 'Statistika',
        8 => 'Profesi Apoteker',
    ];

    /**
     * @param  array{letter_type: string, month: int|null, year: int|null}  $filters
     * @return array{
     *     summary: array{total: int, inProgress: int, archived: int, rejected: int},
     *     programStudiDistribution: array<int, array{label: string, total: int}>,
     *     filterOptions: array{years: array<int, int>}
     * }
     */
    public function overview(array $filters): array
    {
        $summary = [
            'total' => 0,
            'inProgress' => 0,
            'archived' => 0,
            'rejected' => 0,
        ];

        if ($this->includesSidangNol($filters)) {
            $this->appendSidangNolSummary($summary, $filters);
        }

        if ($this->includesCutiAkademik($filters)) {
            $this->appendCutiAkademikSummary($summary, $filters);
        }

        return [
            'summary' => $summary,
            'programStudiDistribution' => $this->programStudiDistribution($filters),
            'filterOptions' => [
                'years' => $this->availableYears($filters['letter_type']),
            ],
        ];
    }

    /** @param array{total: int, inProgress: int, archived: int, rejected: int} $summary */
    private function appendSidangNolSummary(array &$summary, array $filters): void
    {
        $counts = $this->sidangNolQuery($filters)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ([1 => 'inProgress', 2 => 'archived', 3 => 'rejected'] as $status => $key) {
            $total = (int) ($counts[$status] ?? 0);
            $summary['total'] += $total;
            $summary[$key] += $total;
        }
    }

    /** @param array{total: int, inProgress: int, archived: int, rejected: int} $summary */
    private function appendCutiAkademikSummary(array &$summary, array $filters): void
    {
        $counts = $this->cutiAkademikQuery($filters)
            ->selectRaw('letters.status, COUNT(*) as total')
            ->groupBy('letters.status')
            ->pluck('total', 'status');

        foreach (['IN_PROGRESS' => 'inProgress', 'ARCHIVED' => 'archived', 'REJECTED' => 'rejected'] as $status => $key) {
            $total = (int) ($counts[$status] ?? 0);
            $summary['total'] += $total;
            $summary[$key] += $total;
        }
    }

    /**
     * @param  array{letter_type: string, month: int|null, year: int|null}  $filters
     * @return array<int, array{label: string, total: int}>
     */
    private function programStudiDistribution(array $filters): array
    {
        $distribution = [];

        if ($this->includesSidangNol($filters)) {
            $rows = $this->sidangNolQuery($filters)
                ->join('users', 'berkas_sidang_nol.user_id', '=', 'users.id')
                ->select('users.program_studi', DB::raw('COUNT(*) as total'))
                ->groupBy('users.program_studi')
                ->get();

            foreach ($rows as $row) {
                $this->addDistributionTotal(
                    $distribution,
                    self::PROGRAM_STUDI[(int) $row->program_studi] ?? 'Tidak Diketahui',
                    (int) $row->total,
                );
            }
        }

        if ($this->includesCutiAkademik($filters)) {
            $rows = $this->cutiAkademikQuery($filters)
                ->select('letters.program_studi', DB::raw('COUNT(*) as total'))
                ->groupBy('letters.program_studi')
                ->get();

            foreach ($rows as $row) {
                $this->addDistributionTotal(
                    $distribution,
                    $row->program_studi ?: 'Tidak Diketahui',
                    (int) $row->total,
                );
            }
        }

        arsort($distribution);

        return collect($distribution)
            ->map(fn (int $total, string $label): array => ['label' => $label, 'total' => $total])
            ->values()
            ->all();
    }

    /** @param array<string, int> $distribution */
    private function addDistributionTotal(array &$distribution, string $label, int $total): void
    {
        $distribution[$label] = ($distribution[$label] ?? 0) + $total;
    }

    /** @return array<int, int> */
    private function availableYears(string $letterType): array
    {
        $years = collect([now()->year]);

        if ($letterType !== 'cuti-akademik') {
            $years = $years->merge(
                $this->sidangNolBaseQuery()
                    ->whereNotNull('tanggal_dikirim')
                    ->selectRaw($this->yearExpression('tanggal_dikirim').' as year')
                    ->distinct()
                    ->pluck('year'),
            );
        }

        if ($letterType !== 'sidang-nol') {
            $years = $years->merge(
                $this->cutiAkademikBaseQuery()
                    ->whereNotNull('letters.submitted_at')
                    ->selectRaw($this->yearExpression('letters.submitted_at').' as year')
                    ->distinct()
                    ->pluck('year'),
            );
        }

        return $years
            ->filter()
            ->map(fn ($year): int => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /** @param array{letter_type: string, month: int|null, year: int|null} $filters */
    private function sidangNolQuery(array $filters): Builder
    {
        return $this->applyDateFilters($this->sidangNolBaseQuery(), 'tanggal_dikirim', $filters);
    }

    private function sidangNolBaseQuery(): Builder
    {
        return DB::table('berkas_sidang_nol')->whereIn('status', [1, 2, 3]);
    }

    /** @param array{letter_type: string, month: int|null, year: int|null} $filters */
    private function cutiAkademikQuery(array $filters): Builder
    {
        return $this->applyDateFilters($this->cutiAkademikBaseQuery(), 'letters.submitted_at', $filters);
    }

    private function cutiAkademikBaseQuery(): Builder
    {
        return DB::table('letters')
            ->join('letter_types', 'letters.type_id', '=', 'letter_types.id')
            ->where('letter_types.code', 'CUTI_AKADEMIK')
            ->whereIn('letters.status', ['IN_PROGRESS', 'ARCHIVED', 'REJECTED']);
    }

    /** @param array{letter_type: string, month: int|null, year: int|null} $filters */
    private function applyDateFilters(Builder $query, string $column, array $filters): Builder
    {
        return $query
            ->when($filters['year'], fn (Builder $query, int $year) => $query->whereYear($column, $year))
            ->when($filters['month'], fn (Builder $query, int $month) => $query->whereMonth($column, $month));
    }

    private function includesSidangNol(array $filters): bool
    {
        return $filters['letter_type'] !== 'cuti-akademik';
    }

    private function includesCutiAkademik(array $filters): bool
    {
        return $filters['letter_type'] !== 'sidang-nol';
    }

    private function yearExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }
}
