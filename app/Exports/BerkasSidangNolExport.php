<?php

namespace App\Exports;

use App\Models\BerkasSidangNol;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BerkasSidangNolExport implements
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
        $baseUrl = config('app.url') . '/api/berkas-sidang-nol/';

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
            1 => 'Dikirim',
            2 => 'Diterima',
            3 => 'Ditolak',
        ];

        Carbon::setLocale('id');

        $query = BerkasSidangNol::with('user');

        if ($this->status == 99) {
            $query->where('status', '!=', 0);
        } else {
            $query->where('status', $this->status);
        }

        if ($this->programStudi != 99) {
            $query->whereHas('user', function ($q) {
                $q->where('program_studi', $this->programStudi);
            });
        }

        if ($this->tahun != 99) {
            $query->whereYear('tanggal_dikirim', $this->tahun);
        }

        return $query->get()
            ->map(function ($item) use ($baseUrl, $prodiMap, $statusMap) {
                $makeLink = fn($path) => $path
                    ? '=HYPERLINK("' . $baseUrl . ltrim(Str::after($path, 'berkas_sidang_nol/'), '/') . '", "Lihat")'
                    : '-';

                return [
                    $item->nomor_surat,
                    optional($item->user)->nama,
                    optional($item->user)->nim,
                    $prodiMap[optional($item->user)->program_studi] ?? '-',
                    $statusMap[$item->status] ?? '-',
                    $item->tanggal_dikirim ? Carbon::parse($item->tanggal_dikirim)->translatedFormat('l, d F Y') : '-',
                    $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai)->translatedFormat('l, d F Y') : '-',
                    $makeLink($item->dokumen_hasil_studi),
                    $makeLink($item->dokumen_data_diri),
                    $makeLink($item->dokumen_pddikti_ukt),
                    $makeLink($item->dokumen_ruangbaca_laboratorium_pkkmb_skpi),
                    $makeLink($item->dokumen_office_toefl),
                    $makeLink($item->dokumen_tambahan),
                    $makeLink($item->surat_balasan),
                ];
            })->toArray();
    }


    public function headings(): array
    {
        return [
            'Nomor Surat',
            'Nama Mahasiswa',
            'NIM Mahasiswa',
            'Program Studi',
            'Status',
            'Tanggal Dikirim',
            'Tanggal Selesai',
            'Dokumen Hasil',
            'Dokumen Data Diri',
            'Dokumen PDDIKTI',
            'Dokumen Ruang',
            'Dokumen Office',
            'Dokumen Tambahan',
            'Surat Balasan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Warna kuning untuk header
        $sheet->getStyle('A1:N1')->applyFromArray([
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
        $sheet->getStyle("A1:N$highestRow")->applyFromArray([
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
            'D' => 20,
            'E' => 15,
            'F' => 20,
            'G' => 20,
            'H' => 30,
            'I' => 30,
            'J' => 30,
            'K' => 30,
            'L' => 30,
            'M' => 30,
            'N' => 30,
        ];
    }
}
