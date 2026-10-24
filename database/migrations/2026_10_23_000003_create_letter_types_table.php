<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('category', 32)->index();
            $table->string('name', 200);
            $table->longText('notes_markdown')->nullable();
            $table->json('approval_chain')->nullable();
            $table->json('applicant_roles')->nullable();
            $table->string('pdf_template', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_types');
    }
};
