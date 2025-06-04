<?php

namespace App\Exports;

use App\Models\BerkasPersuratan;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class BerkasPersuratanExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $tahun;
    protected $status;
    protected $programStudi;

    public function __construct($tahun, $status, $programStudi)
    {
        $this->tahun = $tahun;
        $this->status = $status;
        $this->programStudi = $programStudi;
    }

    public function array(): array
    {
        $baseUrl = config('app.url') . '/api/berkas-persuratan/';

        $prodiMap = [
            1 => 'Matematika',
            2 => 'Kimia',
            3 => 'Biologi',
            4 => 'Fisika',
            5 => 'Farmasi',
            6 => 'Ilmu Komputer',
            7 => 'Statistika',
            8 => 'Profesi Apoteker',
        ];

        $statusMap = [
            11 => 'Draft',
            21 => 'Menunggu Resepsionis',
            22 => 'Diterima Resepsionis',
            23 => 'Ditolak Resepsionis',
            31 => 'Menunggu Dekan',
            32 => 'Diterima Dekan',
            33 => 'Ditolak Dekan',
            41 => 'Menunggu Wakil Dekan',
            42 => 'Diterima Wakil Dekan',
            43 => 'Ditolak Wakil Dekan',
            51 => 'Menunggu Kepala TU',
            52 => 'Diterima Kepala TU',
            53 => 'Ditolak Kepala TU',
            61 => 'Menunggu Sub Akademik',
            62 => 'Diterima Sub Akademik',
            63 => 'Ditolak Sub Akademik',
            71 => 'Menunggu Tandatangan',
            81 => 'Menunggu Sub Layanan',
            91 => 'Selesai',
        ];

        $query = BerkasPersuratan::with('user');

        // Status Filter
        if ($this->status == 1) {
            $query->where('status', 21);
        } elseif ($this->status == 2) {
            $query->whereBetween('status', [31, 81]);
        } elseif ($this->status == 3) {
            $query->where('status', 91);
        }

        // Prodi Filter
        if ($this->programStudi != 99) {
            $query->whereHas('user', function ($q) {
                $q->where('program_studi', $this->programStudi);
            });
        }

        // Tahun Filter
        if ($this->tahun != 99) {
            $query->whereYear('tanggal_dikirim', $this->tahun);
        }

        // Format tanggal lokal
        Carbon::setLocale('id');

        return $query->get()
            ->map(function ($item) use ($baseUrl, $prodiMap, $statusMap) {
                $makeLinkList = function ($jsonPaths) use ($baseUrl) {
                    $paths = json_decode($jsonPaths, true) ?? [];
                    $pdfs = array_filter($paths, fn($p) => strtolower(pathinfo($p, PATHINFO_EXTENSION)) === 'pdf');

                    if (empty($pdfs)) return '-';

                    return collect($pdfs)->values()->map(function ($path, $i) use ($baseUrl) {
                        $file = ltrim(Str::after($path, 'berkas_persuratan/'), '/');
                        return '=HYPERLINK("' . $baseUrl . $file . '", "Lihat berkas ' . ($i + 1) . '")';
                    })->implode("\n");
                };

                return [
                    $item->nomor_surat,
                    optional($item->user)->nama,
                    optional($item->user)->nim,
                    $item->keterangan,
                    $prodiMap[optional($item->user)->program_studi] ?? '-',
                    $statusMap[$item->status] ?? '-',
                    $item->tanggal_dikirim ? Carbon::parse($item->tanggal_dikirim)->translatedFormat('l, d F Y') : '-',
                    $makeLinkList($item->berkas_mahasiswa),
                    $makeLinkList($item->berkas_balasan),
                    $makeLinkList($item->berkas_tambahan),
                ];
            })->toArray();
    }



    public function headings(): array
{
    return [
        'Nomor Surat',
        'Nama Mahasiswa',
        'NIM Mahasiswa',
        'Keterangan',
        'Program Studi',
        'Status',
        'Tanggal Dikirim',
        'Berkas Mahasiswa',
        'Berkas Balasan',
        'Berkas Tambahan',
    ];
}


    public function styles(Worksheet $sheet)
    {
        // Warna kuning untuk header
        $sheet->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFF00'], // Kuning
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:J$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 20,
            'D' => 40,
            'E' => 15,
            'F' => 20,
            'G' => 20,
            'H' => 30,
            'I' => 30,
            'J' => 30,
        ];
    }
}
