<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class User_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'id' => 20,
            'role_id' => 8,
            'program_studi' => null,
            'nama' => 'Admin SIARSIP',
            'email' => 'admin2@siarsip.test',
            'nomor_telpon' => '08976488192',
            'nim' => '8',
            'password' => Hash::make('asdfghjkl') // atau sesuaikan default passwordnya
        ]);

        // Dummy user
        User::factory(10)->create();
    }
}
