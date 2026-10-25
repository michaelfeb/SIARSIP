<?php

namespace Database\Seeders;

use App\Models\LetterType;
use Illuminate\Database\Seeder;

class CutiAkademikLetterTypeSeeder extends Seeder
{
    public function run(): void
    {
        LetterType::query()->updateOrCreate(
            ['code' => 'CUTI_AKADEMIK'],
            [
                'category' => 'AKADEMIK',
                'name' => 'Cuti Akademik',
                'notes_markdown' => $this->notesMarkdown(),
                'approval_chain' => ['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS'],
                'applicant_roles' => ['MAHASISWA'],
                'pdf_template' => 'letters.cuti-akademik',
            ],
        );
    }

    private function notesMarkdown(): string
    {
        return <<<'MARKDOWN'
Panduan Pengisian Surat Cuti Akademik

**Tujuan surat ini:**

Memberikan izin resmi kepada mahasiswa untuk mengambil cuti akademik selama periode tertentu tanpa kehilangan status sebagai mahasiswa aktif.

**Data yang diperlukan:**

1. **Nama Mahasiswa**: Nama lengkap sesuai kartu identitas (otomatis terisi)
2. **NIM**: Nomor Induk Mahasiswa (otomatis terisi)
3. **Program Studi**: Program studi mahasiswa (otomatis terisi)
4. **Nomor Kontak**: Nomor telepon atau WhatsApp yang aktif
5. **Nominal UKT**: Nominal UKT/Biaya pendidikan per semester
6. **Tujuan**: Alasan pengambilan cuti akademik (misal: sakit, keperluan keluarga, dll)

**Catatan penting:**

1. Cuti akademik hanya berlaku untuk 1 semester
2. Mahasiswa dapat mengambil maksimal 2 semester cuti berturut-turut
3. Setelah cuti berakhir, mahasiswa wajib mendaftar ulang semester berikutnya
4. Nomor kontak harus aktif untuk komunikasi lebih lanjut
5. Dokumen pendukung seperti surat sakit dari rumah sakit atau surat keterangan keluarga dapat dilampirkan bila tersedia

**Alur persetujuan:**

1. **PRODI**: Verifikasi kelengkapan data dan riwayat akademik mahasiswa
2. **AKADEMIK**: Verifikasi kelengkapan administratif dan status pembayaran
3. **KTU**: Verifikasi
4. **DEKAN**: Persetujuan final
5. **RESEPSIONIS**: Nomor surat, generate PDF, dan pengarsipan

**Timeline persetujuan:**

1. PRODI: 1 hari kerja
2. AKADEMIK: 1 hari kerja
3. KTU: 1 hari kerja
4. DEKAN: 1 hari kerja
5. RESEPSIONIS: 1 hari kerja

**Setelah disetujui:**

Surat akan di-generate otomatis dalam format PDF dengan nomor surat resmi. Mahasiswa dapat mengunduh dan mencetak untuk keperluan administratif.
MARKDOWN;
    }
}
