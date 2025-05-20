<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'berkas_id')) {
                $table->dropColumn('berkas_id');
            }

            if (Schema::hasColumn('notes', 'berkas_persuratan_id')) {
                $table->dropForeign(['berkas_persuratan_id']); // drop FK dulu jika ada
                $table->dropColumn('berkas_persuratan_id');
            }

            $table->unsignedBigInteger('berkas_id')->after('id');

            if (!Schema::hasColumn('notes', 'jenis_berkas')) {
                $table->enum('jenis_berkas', ['1', '2'])
                    ->after('berkas_id')
                    ->comment('1 = Berkas Persuratan, 2 = Berkas Sidang Nol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['berkas_id', 'jenis_berkas']);
        });
    }
};
