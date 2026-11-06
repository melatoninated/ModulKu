<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan seluruh kategori.
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return response()->json([
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => $categories,
        ]);
    }

    /**
     * Menambahkan kategori (khusus admin).
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
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    /**
     * Menampilkan detail kategori.
     */
    public function show(Category $category)
    {
        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $category,
        ]);
    }

    /**
     * Mengubah kategori (khusus admin).
     */
    public function update(Request $request, Category $category)
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
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    /**
     * Menghapus kategori (khusus admin).
     */
    public function destroy(Request $request, Category $category)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        if ($category->materials()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh materi.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
