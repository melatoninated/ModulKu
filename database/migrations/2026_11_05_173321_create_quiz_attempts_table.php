<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            // Siswa yang mengerjakan kuis
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Kuis yang dikerjakan
            $table->foreignId('quiz_id')
                  ->constrained('quizzes')
                  ->cascadeOnDelete();

            // Hasil pengerjaan
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('total_questions')->default(0);

            // Waktu pengerjaan
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Status pengerjaan
            $table->enum('status', [
                'in_progress',
                'completed'
            ])->default('in_progress');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
