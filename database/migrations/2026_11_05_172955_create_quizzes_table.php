<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            // Relasi ke mata pelajaran
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnDelete();

            // Admin yang membuat kuis
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Informasi kuis
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Pengaturan kuis
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('passing_score')->default(70);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
