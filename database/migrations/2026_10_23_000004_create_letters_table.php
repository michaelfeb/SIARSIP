<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('type_id')->constrained('letter_types')->restrictOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('applicant_name', 200);
            $table->string('applicant_id_number', 50);
            $table->string('program_studi', 100)->nullable();
            $table->string('category', 32);
            $table->longText('purpose')->nullable();
            $table->string('status', 32)->default('DRAFT');
            $table->string('current_stage', 32)->nullable();
            $table->string('letter_number', 64)->nullable()->unique();
            $table->date('issued_at')->nullable();
            $table->longText('notes_snapshot')->nullable();
            $table->json('meta')->nullable();
            $table->string('final_pdf_path', 512)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->boolean('pdf_error')->default(false);
            $table->json('pdf_error_meta')->nullable();
            $table->timestamps();

            $table->index(['type_id', 'status']);
            $table->index('current_stage');
            $table->index('applicant_id');
            $table->index('submitted_at');
            $table->index(['category', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
