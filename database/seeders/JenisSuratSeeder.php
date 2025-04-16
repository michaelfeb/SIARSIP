<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisSurat::insert([
            ['nama' => 'Surat Cuti', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Surat Magang', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
