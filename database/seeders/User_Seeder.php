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
        User::create(
            [
            'nama' => 'Admin SIARSIP',
            'email' => 'admin@siarsip.test',
            'password' => Hash::make('asdfghjkl'),
            'waktu_email_terverifikasi' => now(),
            ]
        );

        // Dummy user
        User::factory(10)->create();
    }
}
