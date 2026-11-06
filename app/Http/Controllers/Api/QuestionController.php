<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Memeriksa apakah kuis sudah pernah dikerjakan siswa.
     */
    private function hasAttempts(Quiz $quiz): bool
    {
        return $quiz->attempts()->exists();
    }

    /**
     * Menambahkan pertanyaan baru.
     */
    public function store(Request $request, Quiz $quiz)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        if ($this->hasAttempts($quiz)) {
            return response()->json([
                'message' => 'Pertanyaan tidak dapat ditambahkan karena kuis sudah memiliki riwayat pengerjaan.',
            ], 422);
        }

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => [
                'required',
                'in:multiple_choice,true_false',
            ],
            'points' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:1'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.option_text' => [
                'required',
                'string',
                'max:255',
            ],
            'options.*.is_correct' => [
                'required',
                'boolean',
            ],
        ]);

        $correctCount = collect($validated['options'])
            ->where('is_correct', true)
            ->count();

        if ($correctCount !== 1) {
            return response()->json([
                'message' => 'Setiap pertanyaan harus memiliki tepat satu jawaban benar.',
            ], 422);
        }

        $question = DB::transaction(function () use ($validated, $quiz) {
            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points'] ?? 1,
                'order' => $validated['order'] ?? 1,
            ]);

            foreach ($validated['options'] as $index => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $index + 1,
                ]);
            }

            return $question;
        });

        return response()->json([
            'message' => 'Pertanyaan berhasil ditambahkan.',
            'data' => $question->load('options'),
        ], 201);
    }

    /**
     * Mengubah pertanyaan.
     */
    public function update(Request $request, Question $question)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        if ($this->hasAttempts($question->quiz)) {
            return response()->json([
                'message' => 'Pertanyaan tidak dapat diubah karena kuis sudah memiliki riwayat pengerjaan.',
            ], 422);
        }

        $validated = $request->validate([
            'question_text' => [
                'sometimes',
                'required',
                'string',
            ],
            'question_type' => [
                'sometimes',
                'required',
                'in:multiple_choice,true_false',
            ],
            'points' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'order' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'options' => [
                'sometimes',
                'required',
                'array',
                'min:2',
            ],
            'options.*.option_text' => [
                'required',
                'string',
                'max:255',
            ],
            'options.*.is_correct' => [
                'required',
                'boolean',
            ],
        ]);

        if (isset($validated['options'])) {
            $correctCount = collect($validated['options'])
                ->where('is_correct', true)
                ->count();

            if ($correctCount !== 1) {
                return response()->json([
                    'message' => 'Setiap pertanyaan harus memiliki tepat satu jawaban benar.',
                ], 422);
            }
        }

        DB::transaction(function () use ($validated, $question) {
            $options = $validated['options'] ?? null;

            unset($validated['options']);

            $question->update($validated);

            if ($options !== null) {
                $question->options()->delete();

                foreach ($options as $index => $option) {
                    $question->options()->create([
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'],
                        'order' => $index + 1,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Pertanyaan berhasil diperbarui.',
            'data' => $question->fresh()->load('options'),
        ]);
    }

    /**
     * Menghapus pertanyaan.
     */
    public function destroy(Request $request, Question $question)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        if ($this->hasAttempts($question->quiz)) {
            return response()->json([
                'message' => 'Pertanyaan tidak dapat dihapus karena kuis sudah memiliki riwayat pengerjaan.',
            ], 422);
        }

        $question->delete();

        return response()->json([
            'message' => 'Pertanyaan berhasil dihapus.',
        ]);
    }
}
