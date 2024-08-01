<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag; // Pastikan ini diimpor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Menampilkan daftar tag.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tag = Tag::latest()->get(); // Gunakan nama variabel yang lebih jelas

        $res = [
            'success' => true,
            'message' => 'Daftar Tag', // Sesuaikan pesan dengan konteks
            'data' => $tags,
        ];
        return response()->json($res, 200);
    }

    /**
     * Menyimpan tag baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_tag' => 'required|unique:tags',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Simpan data tag
            $tag = new Tag();
            $tag->nama_tag = $request->nama_tag;
            $tag->slug = Str::slug($request->nama_tag);
            $tag->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dibuat',
                'data' => $tag,
            ], 201);
        } catch (\Exception $e) {
            // Tangani kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail tag berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Cari tag berdasarkan ID
        $tag = Tag::find($id);

        // Jika tag ditemukan
        if ($tag) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Tag', // Sesuaikan pesan dengan konteks
                'data' => $tag,
            ], 200);
        }

        // Jika tag tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Tag tidak ditemukan',
        ], 404);
    }

    /**
     * Memperbarui tag yang ada.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_tag' => [
                'required',
                'unique:tags,nama_tag,' . $id,
            ],
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Cari dan perbarui tag berdasarkan ID
            $tag = Tag::findOrFail($id);
            $tag->nama_tag = $request->nama_tag;
            $tag->slug = Str::slug($request->nama_tag);
            $tag->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $tag,
            ], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus tag berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $tag = Tag::findOrFail($id);
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak ditemukan',
            ], 404);
        }
    }
}
