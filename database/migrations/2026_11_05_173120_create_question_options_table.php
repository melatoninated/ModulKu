<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();

            // Relasi ke pertanyaan
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();

            // Isi pilihan jawaban
            $table->string('option_text');

            // Menentukan apakah pilihan merupakan jawaban benar
            $table->boolean('is_correct')->default(false);

            // Urutan pilihan jawaban
            $table->unsignedInteger('order')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
