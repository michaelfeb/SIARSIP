<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Mahasiswa',
            'Resepsionis',
            'Dekan',
            'WD',
            'TU',
            'Bagian Akademik',
            'Bagian Layanan',
            'Super Admin',
            'Admin TTPS'
        ];

        foreach ($roles as $role) {
            Role::create(['nama' => $role]);
        }
    }
}
