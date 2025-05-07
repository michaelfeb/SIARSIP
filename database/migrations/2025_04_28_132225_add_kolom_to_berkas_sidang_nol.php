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
        Schema::table('berkas_sidang_nol', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable()->after('user_id');
            $table->date('tanggal_dikirim')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berkas_sidang_nol', function (Blueprint $table) {
            $table->dropColumn('nomor_surat');
            $table->dropColumn('tanggal_dikirim');
        });
    }
};
