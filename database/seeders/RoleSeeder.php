<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $roles = [
            1 => 'MAHASISWA',
            2 => 'RESEPSIONIS',
            3 => 'DEKAN',
            4 => 'WD1',
            5 => 'KTU',
            6 => 'AKADEMIK',
            7 => 'BAGIAN LAYANAN',
            8 => 'SUPER ADMIN',
            9 => 'ADMIN TTPS',
            10 => 'WD2',
            11 => 'WD3',
            12 => 'PRODI',
        ];

        Role::query()->upsert(
            collect($roles)->map(fn (string $nama, int $id) => [
                'id' => $id,
                'nama' => $nama,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['id'],
            ['nama', 'updated_at'],
        );
    }
}
