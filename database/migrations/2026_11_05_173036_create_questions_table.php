<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            // Relasi ke kuis
            $table->foreignId('quiz_id')
                  ->constrained('quizzes')
                  ->cascadeOnDelete();

            // Isi pertanyaan
            $table->text('question_text');

            // Jenis pertanyaan
            $table->enum('question_type', [
                'multiple_choice',
                'true_false'
            ])->default('multiple_choice');

            // Bobot nilai pertanyaan
            $table->unsignedInteger('points')->default(1);

            // Urutan pertanyaan dalam kuis
            $table->unsignedInteger('order')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
