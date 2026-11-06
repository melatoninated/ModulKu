<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Menampilkan daftar kuis.
     */
    public function index()
    {
        $quizzes = Quiz::with(['subject', 'creator:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar kuis berhasil diambil.',
            'data' => $quizzes,
        ]);
    }

    /**
     * Membuat kuis baru.
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'title' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'passing_score' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
        ]);

        $quiz = Quiz::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Kuis berhasil dibuat.',
            'data' => $quiz->load([
                'subject',
                'creator:id,name',
            ]),
        ], 201);
    }

    /**
     * Menampilkan detail kuis.
     */
    public function show(Request $request, Quiz $quiz)
    {
        $quiz->load([
            'subject',
            'creator:id,name',
            'questions.options',
        ]);

        // Sembunyikan kunci jawaban dari siswa.
        if ($request->user()->role === 'siswa') {
            foreach ($quiz->questions as $question) {
                foreach ($question->options as $option) {
                    $option->makeHidden('is_correct');
                }
            }
        }

        return response()->json([
            'message' => 'Detail kuis berhasil diambil.',
            'data' => $quiz,
        ]);
    }

    /**
     * Mengubah informasi kuis.
     */
    public function update(Request $request, Quiz $quiz)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'subject_id' => [
                'sometimes',
                'required',
                'exists:subjects,id',
            ],
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'duration' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
            'passing_score' => [
                'sometimes',
                'integer',
                'min:0',
                'max:100',
            ],
        ]);

        // Jika kuis sudah pernah dikerjakan, informasi yang
        // memengaruhi penilaian dan struktur kuis tidak boleh diubah.
        if ($quiz->attempts()->exists()) {
            $protectedFields = [
                'subject_id',
                'duration',
                'passing_score',
            ];

            $changedProtectedFields = array_intersect(
                array_keys($validated),
                $protectedFields
            );

            if (!empty($changedProtectedFields)) {
                return response()->json([
                    'message' => 'Mata pelajaran, durasi, dan nilai kelulusan tidak dapat diubah karena kuis sudah memiliki riwayat pengerjaan.',
                ], 422);
            }
        }

        $quiz->update($validated);

        return response()->json([
            'message' => 'Kuis berhasil diperbarui.',
            'data' => $quiz->load([
                'subject',
                'creator:id,name',
            ]),
        ]);
    }

    /**
     * Menghapus kuis.
     */
    public function destroy(Request $request, Quiz $quiz)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        // Lindungi riwayat pengerjaan siswa.
        if ($quiz->attempts()->exists()) {
            return response()->json([
                'message' => 'Kuis tidak dapat dihapus karena sudah memiliki riwayat pengerjaan siswa.',
            ], 422);
        }

        $quiz->delete();

        return response()->json([
            'message' => 'Kuis berhasil dihapus.',
        ]);
    }
}
