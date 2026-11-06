<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MaterialController extends Controller
{
    /**
     * Memastikan hanya admin yang dapat mengelola materi.
     */
    private function isAdmin(Request $request): bool
    {
        return $request->user()->role === 'admin';
    }

    /**
     * Mengambil relasi materi untuk response API.
     */
    private function materialRelations(): array
    {
        return [
            'category',
            'subject',
            'creator:id,name',
        ];
    }

    /**
     * Menampilkan seluruh materi.
     */
    public function index()
    {
        $materials = Material::with($this->materialRelations())
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar materi berhasil diambil.',
            'data' => $materials,
        ]);
    }

    /**
     * Menambahkan materi baru.
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
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
            'content' => [
                'nullable',
                'string',
                'required_without_all:pdf_file,video_file',
            ],
            'pdf_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
            'video_file' => [
                'nullable',
                'file',
                'mimes:mp4,mov,avi,webm',
                'max:102400',
            ],
        ]);

        $storedFiles = [];

        try {
            if ($request->hasFile('pdf_file')) {
                $pdfPath = $request->file('pdf_file')
                    ->store('materials/pdfs', 'public');

                if (!$pdfPath) {
                    throw new RuntimeException(
                        'Gagal menyimpan file PDF.'
                    );
                }

                $storedFiles[] = $pdfPath;
            } else {
                $pdfPath = null;
            }

            if ($request->hasFile('video_file')) {
                $videoPath = $request->file('video_file')
                    ->store('materials/videos', 'public');

                if (!$videoPath) {
                    throw new RuntimeException(
                        'Gagal menyimpan file video.'
                    );
                }

                $storedFiles[] = $videoPath;
            } else {
                $videoPath = null;
            }

            $material = DB::transaction(function () use (
                $validated,
                $request,
                $pdfPath,
                $videoPath
            ) {
                return Material::create([
                    'category_id' => $validated['category_id'],
                    'subject_id' => $validated['subject_id'],
                    'created_by' => $request->user()->id,
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'content' => $validated['content'] ?? null,
                    'pdf_url' => $pdfPath,
                    'video_url' => $videoPath,
                ]);
            });

        } catch (Throwable $exception) {
            // Hapus file baru jika proses penyimpanan gagal.
            if (!empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data' => $material->load($this->materialRelations()),
        ], 201);
    }

    /**
     * Menampilkan detail materi.
     */
    public function show(Material $material)
    {
        return response()->json([
            'message' => 'Detail materi berhasil diambil.',
            'data' => $material->load($this->materialRelations()),
        ]);
    }

    /**
     * Memperbarui materi.
     */
    public function update(Request $request, Material $material)
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                'exists:categories,id',
            ],
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
            'content' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'pdf_file' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
            'video_file' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:mp4,mov,avi,webm',
                'max:102400',
            ],
        ]);

        /*
         * Pastikan materi tetap memiliki minimal satu sumber
         * berupa teks, PDF, atau video setelah diperbarui.
         */
        $hasContent = array_key_exists('content', $validated)
            ? filled($validated['content'])
            : filled($material->content);

        $hasPdf = $request->hasFile('pdf_file')
            || filled($material->pdf_url);

        $hasVideo = $request->hasFile('video_file')
            || filled($material->video_url);

        if (!$hasContent && !$hasPdf && !$hasVideo) {
            return response()->json([
                'message' => 'Materi harus memiliki setidaknya satu sumber berupa teks, PDF, atau video.',
            ], 422);
        }

        $storedFiles = [];
        $oldFiles = [];

        try {
            if ($request->hasFile('pdf_file')) {
                $newPdfPath = $request->file('pdf_file')
                    ->store('materials/pdfs', 'public');

                if (!$newPdfPath) {
                    throw new RuntimeException(
                        'Gagal menyimpan file PDF.'
                    );
                }

                $storedFiles[] = $newPdfPath;
                $oldFiles[] = $material->pdf_url;

                $validated['pdf_url'] = $newPdfPath;
            }

            if ($request->hasFile('video_file')) {
                $newVideoPath = $request->file('video_file')
                    ->store('materials/videos', 'public');

                if (!$newVideoPath) {
                    throw new RuntimeException(
                        'Gagal menyimpan file video.'
                    );
                }

                $storedFiles[] = $newVideoPath;
                $oldFiles[] = $material->video_url;

                $validated['video_url'] = $newVideoPath;
            }

            // Hapus nama input file dari data yang akan disimpan.
            unset(
                $validated['pdf_file'],
                $validated['video_file']
            );

            DB::transaction(function () use (
                $material,
                $validated
            ) {
                $material->update($validated);
            });

        } catch (Throwable $exception) {
            // Pertahankan file lama jika proses update gagal.
            if (!empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }

            throw $exception;
        }

        /*
         * Hapus file lama setelah update database berhasil.
         */
        $oldFiles = array_filter($oldFiles);

        if (!empty($oldFiles)) {
            Storage::disk('public')->delete($oldFiles);
        }

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data' => $material->fresh()->load(
                $this->materialRelations()
            ),
        ]);
    }

    /**
     * Menghapus materi.
     */
    public function destroy(Request $request, Material $material)
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $files = array_filter([
            $material->pdf_url,
            $material->video_url,
        ]);

        // Hapus data database terlebih dahulu.
        DB::transaction(function () use ($material) {
            $material->delete();
        });

        // Hapus file setelah data berhasil dihapus.
        if (!empty($files)) {
            Storage::disk('public')->delete($files);
        }

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }
}
