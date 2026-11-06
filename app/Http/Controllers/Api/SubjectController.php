<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Menampilkan seluruh mata pelajaran.
     */
    public function index()
    {
        $subjects = Subject::latest()->get();

        return response()->json([
            'message' => 'Daftar mata pelajaran berhasil diambil.',
            'data' => $subjects,
        ]);
    }

    /**
     * Menambahkan mata pelajaran (khusus admin).
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:subjects,name',
            ],
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data' => $subject,
        ], 201);
    }

    /**
     * Menampilkan detail mata pelajaran.
     */
    public function show(Subject $subject)
    {
        return response()->json([
            'message' => 'Detail mata pelajaran berhasil diambil.',
            'data' => $subject,
        ]);
    }

    /**
     * Mengubah mata pelajaran (khusus admin).
     */
    public function update(Request $request, Subject $subject)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:subjects,name,' . $subject->id,
            ],
        ]);

        $subject->update($validated);

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => $subject,
        ]);
    }

    /**
     * Menghapus mata pelajaran (khusus admin).
     */
    public function destroy(Request $request, Subject $subject)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        if (
            $subject->materials()->exists() ||
            $subject->quizzes()->exists()
        ) {
            return response()->json([
                'message' => 'Mata pelajaran tidak dapat dihapus karena masih digunakan oleh materi atau kuis.',
            ], 422);
        }

        $subject->delete();

        return response()->json([
            'message' => 'Mata pelajaran berhasil dihapus.',
        ]);
    }
}
