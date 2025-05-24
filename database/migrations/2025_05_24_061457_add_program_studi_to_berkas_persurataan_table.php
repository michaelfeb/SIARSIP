<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('berkas_persuratan', function (Blueprint $table) {
            $table->unsignedBigInteger('program_studi')->nullable()->after('id'); // atau after kolom terakhir yang relevan
        });
    }

    public function down(): void
    {
        Schema::table('berkas_persuratan', function (Blueprint $table) {
            $table->dropColumn('program_studi');
        });
    }
};
