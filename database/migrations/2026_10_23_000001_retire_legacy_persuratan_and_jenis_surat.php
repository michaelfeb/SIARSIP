<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notes') && Schema::hasColumn('notes', 'jenis_berkas')) {
            DB::table('notes')->where('jenis_berkas', '1')->delete();
        }

        Schema::dropIfExists('berkas_persuratan');
        Schema::dropIfExists('jenis_surat');
    }

    public function down(): void
    {
        throw new LogicException('Legacy Persuratan data was intentionally removed. Restore a database backup instead.');
    }
};
