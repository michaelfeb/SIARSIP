<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('letter_id')->constrained('letters')->cascadeOnDelete();
            $table->string('from_stage', 32)->nullable();
            $table->string('to_stage', 32);
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_routes');
    }
};
