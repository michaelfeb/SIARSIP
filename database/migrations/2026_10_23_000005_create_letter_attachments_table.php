<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('letter_id')->constrained('letters')->cascadeOnDelete();
            $table->string('file_path', 512);
            $table->string('original_name', 255);
            $table->string('mime', 100);
            $table->unsignedInteger('size_bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_attachments');
    }
};
