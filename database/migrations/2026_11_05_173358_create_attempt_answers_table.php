<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();

            // Relasi ke percobaan kuis
            $table->foreignId('attempt_id')
                  ->constrained('quiz_attempts')
                  ->cascadeOnDelete();

            // Pertanyaan yang dijawab
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();

            // Pilihan jawaban siswa (boleh kosong)
            $table->foreignId('selected_option_id')
                  ->nullable()
                  ->constrained('question_options')
                  ->nullOnDelete();

            // Jawaban teks jika diperlukan
            $table->text('answer_text')->nullable();

            // Hasil pemeriksaan jawaban
            $table->boolean('is_correct')->default(false);

            // Nilai yang diperoleh untuk pertanyaan ini
            $table->decimal('points_earned', 5, 2)->default(0);

            $table->timestamps();

            // Satu pertanyaan hanya memiliki satu jawaban
            // dalam satu percobaan kuis
            $table->unique(
                ['attempt_id', 'question_id'],
                'attempt_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
