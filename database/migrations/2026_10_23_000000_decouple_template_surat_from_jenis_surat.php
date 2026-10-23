<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_surat') || ! Schema::hasColumn('template_surat', 'jenis_surat_id')) {
            return;
        }

        Schema::table('template_surat', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_surat_id');
        });
    }

    public function down(): void
    {
        throw new LogicException('Template Surat cannot safely be reattached to legacy Jenis Surat data. Restore a database backup instead.');
    }
};
