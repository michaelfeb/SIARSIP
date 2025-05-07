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
        //
        Schema::create('berkas_sidang_nol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('dokumen_hasil_studi');
            $table->string('dokumen_data_diri');
            $table->string('dokumen_pddikti_ukt');
            $table->string('dokumen_ruangbaca_laboratorium_pkkmb_skpi');
            $table->string('dokumen_office_toefl');
            $table->string('dokumen_tambahan')->nullable();
            $table->integer('status')->default(0); // default Draft
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('berkas_sidang_nol');
    }
};
