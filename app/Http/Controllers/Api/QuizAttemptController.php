<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    /**
     * Menampilkan riwayat pengerjaan kuis.
     */
    public function index(Request $request)
    {
        $query = QuizAttempt::with('quiz:id,title')
            ->latest();

        if ($request->user()->role === 'siswa') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json([
            'message' => 'Riwayat pengerjaan kuis berhasil diambil.',
            'data' => $query->get(),
        ]);
    }

    /**
     * Memulai pengerjaan kuis.
     * Jika masih ada pengerjaan aktif, siswa melanjutkan attempt tersebut.
     */
    public function start(Request $request, Quiz $quiz)
    {
        if ($request->user()->role !== 'siswa') {
            return response()->json([
                'message' => 'Hanya siswa yang dapat mengerjakan kuis.',
            ], 403);
        }

        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            return response()->json([
                'message' => 'Kuis belum memiliki pertanyaan.',
            ], 422);
        }

        $activeAttempt = QuizAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($activeAttempt) {
            $attempt = $activeAttempt;
            $message = 'Anda melanjutkan pengerjaan kuis yang masih aktif.';
            $statusCode = 200;
        } else {
            $attempt = QuizAttempt::create([
                'user_id' => $request->user()->id,
                'quiz_id' => $quiz->id,
                'score' => 0,
                'correct_answers' => 0,
                'total_questions' => $questions->count(),
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            $message = 'Kuis berhasil dimulai.';
            $statusCode = 201;
        }

        // Jangan kirim kunci jawaban kepada siswa.
        foreach ($questions as $question) {
            foreach ($question->options as $option) {
                $option->makeHidden('is_correct');
            }
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'attempt' => $attempt,
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'duration' => $quiz->duration,
                    'questions' => $questions,
                ],
            ],
        ], $statusCode);
    }

    /**
     * Mengumpulkan jawaban dan menghitung nilai secara otomatis.
     */
    public function submit(Request $request, QuizAttempt $attempt)
    {
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke pengerjaan ini.',
            ], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'message' => 'Kuis ini sudah dikumpulkan sebelumnya.',
            ], 422);
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],

            'answers.*.question_id' => [
                'required',
                'integer',
                'distinct',
                'exists:questions,id',
            ],

            'answers.*.selected_option_id' => [
                'nullable',
                'integer',
                'exists:question_options,id',
            ],
        ]);

        $questions = Question::where('quiz_id', $attempt->quiz_id)
            ->with('options')
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        // Pastikan semua pertanyaan dan pilihan jawaban berasal
        // dari kuis yang sedang dikerjakan.
        foreach ($validated['answers'] as $answer) {
            $question = $questions->get($answer['question_id']);

            if (!$question) {
                return response()->json([
                    'message' => 'Pertanyaan tidak termasuk dalam kuis ini.',
                ], 422);
            }

            $selectedOptionId = $answer['selected_option_id'] ?? null;

            if ($selectedOptionId !== null) {
                $optionBelongsToQuestion = $question->options
                    ->contains('id', $selectedOptionId);

                if (!$optionBelongsToQuestion) {
                    return response()->json([
                        'message' => 'Pilihan jawaban tidak sesuai dengan pertanyaan.',
                    ], 422);
                }
            }
        }

        $result = DB::transaction(function () use (
            $validated,
            $attempt,
            $questions,
            $request
        ) {
            // Mengunci attempt agar tidak bisa disubmit bersamaan.
            $lockedAttempt = QuizAttempt::whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAttempt->user_id !== $request->user()->id) {
                return [
                    'error' => 'unauthorized',
                ];
            }

            if ($lockedAttempt->status !== 'in_progress') {
                return [
                    'error' => 'already_submitted',
                ];
            }

            $quiz = Quiz::findOrFail($lockedAttempt->quiz_id);

            // Durasi kuis dihitung dalam menit.
            $timeExpired = false;

            if ($quiz->duration !== null && $lockedAttempt->started_at !== null) {
                $deadline = $lockedAttempt->started_at
                    ->copy()
                    ->addMinutes($quiz->duration);

                $timeExpired = now()->greaterThan($deadline);
            }

            $correctAnswers = 0;
            $earnedPoints = 0;
            $totalPoints = $questions->sum('points');

            // Semua pertanyaan disimpan, termasuk yang tidak dijawab.
            foreach ($questions as $question) {
                $answer = collect($validated['answers'])
                    ->firstWhere('question_id', $question->id);

                // Jika waktu habis, jawaban yang baru dikirim
                // tidak diterima.
                $selectedOptionId = $timeExpired
                    ? null
                    : ($answer['selected_option_id'] ?? null);

                $selectedOption = $selectedOptionId
                    ? $question->options->firstWhere('id', $selectedOptionId)
                    : null;

                $isCorrect = $selectedOption
                    ? (bool) $selectedOption->is_correct
                    : false;

                $pointsEarned = $isCorrect
                    ? $question->points
                    : 0;

                if ($isCorrect) {
                    $correctAnswers++;
                }

                $earnedPoints += $pointsEarned;

                $lockedAttempt->answers()->updateOrCreate(
                    [
                        'question_id' => $question->id,
                    ],
                    [
                        'selected_option_id' => $selectedOptionId,

                        // Simpan teks pilihan sebagai snapshot
                        // agar riwayat tetap dapat dibaca.
                        'answer_text' => $selectedOption
                            ? $selectedOption->option_text
                            : null,

                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ]
                );
            }

            $totalQuestions = $questions->count();

            $score = $totalPoints > 0
                ? ($earnedPoints / $totalPoints) * 100
                : 0;

            $lockedAttempt->update([
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            return [
                'attempt' => $lockedAttempt->fresh()->load([
                    'answers.selectedOption:id,option_text',
                ]),
                'time_expired' => $timeExpired,
            ];
        });

        if (isset($result['error'])) {
            if ($result['error'] === 'unauthorized') {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke pengerjaan ini.',
                ], 403);
            }

            return response()->json([
                'message' => 'Kuis ini sudah dikumpulkan sebelumnya.',
            ], 422);
        }

        // Jangan kirim kolom is_correct kepada siswa.
        foreach ($result['attempt']->answers as $answer) {
            $answer->makeHidden('is_correct');
        }

        if ($result['time_expired']) {
            return response()->json([
                'message' => 'Waktu pengerjaan telah habis. Kuis ditutup dan jawaban tidak diterima.',
                'data' => $result['attempt'],
            ]);
        }

        return response()->json([
            'message' => 'Kuis berhasil dikumpulkan.',
            'data' => $result['attempt'],
        ]);
    }

    /**
     * Melihat detail satu pengerjaan.
     */
    public function show(Request $request, QuizAttempt $attempt)
    {
        if (
            $request->user()->role === 'siswa' &&
            $attempt->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke riwayat ini.',
            ], 403);
        }

        $attempt->load([
            'quiz:id,title',
            'answers.question:id,question_text,points',
            'answers.selectedOption:id,option_text',
        ]);

        // Sembunyikan is_correct dari seluruh respons siswa.
        if ($request->user()->role === 'siswa') {
            foreach ($attempt->answers as $answer) {
                $answer->makeHidden('is_correct');
            }
        }

        return response()->json([
            'message' => 'Detail pengerjaan kuis berhasil diambil.',
            'data' => $attempt,
        ]);
    }
}
