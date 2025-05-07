<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('berkas_sidang_nol', function (Blueprint $table) {
            $table->string('surat_balasan')->nullable()->after('dokumen_tambahan');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_dikirim');
        });
    }

    public function down()
    {
        Schema::table('berkas_sidang_nol', function (Blueprint $table) {
            $table->dropColumn('surat_balasan');
            $table->dropColumn('tanggal_selesai');
        });
    }
};
