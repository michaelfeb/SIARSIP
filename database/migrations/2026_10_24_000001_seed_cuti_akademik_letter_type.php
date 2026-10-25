<?php

use Database\Seeders\CutiAkademikLetterTypeSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(CutiAkademikLetterTypeSeeder::class)->run();
    }

    public function down(): void
    {
        // Letter Types are configuration data. Do not delete a type that may
        // already own production Letter history during a rollback.
    }
};
