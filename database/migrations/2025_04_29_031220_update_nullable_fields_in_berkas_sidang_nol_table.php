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
            $table->string('dokumen_hasil_studi')->nullable()->change();
            $table->string('dokumen_data_diri')->nullable()->change();
            $table->string('dokumen_pddikti_ukt')->nullable()->change();
            $table->string('dokumen_ruangbaca_laboratorium_pkkmb_skpi')->nullable()->change();
            $table->string('dokumen_office_toefl')->nullable()->change();
            $table->string('dokumen_tambahan')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('berkas_sidang_nol', function (Blueprint $table) {
            $table->string('dokumen_hasil_studi')->nullable(false)->change();
            $table->string('dokumen_data_diri')->nullable(false)->change();
            $table->string('dokumen_pddikti_ukt')->nullable(false)->change();
            $table->string('dokumen_ruangbaca_laboratorium_pkkmb_skpi')->nullable(false)->change();
            $table->string('dokumen_office_toefl')->nullable(false)->change();
            $table->string('dokumen_tambahan')->nullable(false)->change();
        });
    }
};
