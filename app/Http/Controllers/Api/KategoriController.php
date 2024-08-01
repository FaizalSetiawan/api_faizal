<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $kategori = Kategori::latest()->get();
        $res = [
            'success' => true,
            'message' => 'Daftar Kategori',
            'data' => $kategori,
        ];
        return response()->json($res, 200);
    }

    /**
     * Menyimpan kategori baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|unique:kategoris',
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
            // Simpan data kategori
            $kategori = new Kategori();
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->slug = Str::slug($request->nama_kategori);
            $kategori->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dibuat',
                'data' => $kategori,
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
     * Menampilkan detail kategori berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Cari kategori berdasarkan ID
        $kategori = Kategori::find($id);

        // Jika kategori ditemukan
        if ($kategori) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Kategori',
                'data' => $kategori,
            ], 200);
        }

        // Jika kategori tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], 404);
    }

    /**
     * Memperbarui kategori berdasarkan ID.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_kategori' => [
                'required',
                'unique:kategoris,nama_kategori,' . $id,
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
            // Cari dan perbarui kategori berdasarkan ID
            $kategori = Kategori::findOrFail($id);
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->slug = Str::slug($request->nama_kategori);
            $kategori->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $kategori,
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
    public function destroy($id)
    {
        $kategori = Kategori::find($id);
        $kategori->delete();
        if ($kategori) {
            return response()->json([
                'success' => true,
                'message' => 'Data Berhasil Terhapus',
            ], 200);
        }

        // Jika kategori tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], 404);
    }
}
