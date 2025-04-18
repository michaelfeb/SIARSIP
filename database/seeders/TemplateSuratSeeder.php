<?php

namespace Database\Seeders;

use App\Models\TemplateSurat;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TemplateSurat::create([
            'jenis_surat_id' => 1,
            'nama' => 'Template Surat Cuti',
            'deskripsi' => 'Template resmi untuk surat cuti mahasiswa.',
            'status' => true,
            'dokumen_path' => 'template/cuti.docx',
            'tanggal_publish' => now(),
        ]);
    }
}
