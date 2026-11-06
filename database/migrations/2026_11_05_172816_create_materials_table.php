<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();

            // Relasi ke kategori dan mata pelajaran
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->cascadeOnDelete();

            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnDelete();

            // Admin yang membuat materi
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Informasi materi
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Konten materi
            $table->longText('content')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('video_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
