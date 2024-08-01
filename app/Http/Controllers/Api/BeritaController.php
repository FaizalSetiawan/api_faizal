<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $berita = Berita::with('kategori', 'tags', 'user')->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar berita',
            'data' => $berita,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul' => 'required|unique:beritas',
            'deskripsi' => 'required',
            'foto' => 'required|image|mimes:png,jpg|max:2048',
            'id_kategori' => 'required|exists:kategoris,id',
            'tag' => 'required|array',
            'id_user' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Simpan foto berita
            $path = $request->file('foto')->store('public/berita');

            // Buat berita baru
            $berita = new Berita();
            $berita->judul = $request->judul;
            $berita->deskripsi = $request->deskripsi;
            $berita->foto = $path;
            $berita->id_user = $request->id_user;
            $berita->id_kategori = $request->id_kategori;
            $berita->save();

            // Hubungkan tag dengan berita
            $berita->tags()->attach($request->tag);

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil dibuat',
                'data' => $berita,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $berita = Berita::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail berita',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan',
                'errors' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul' => 'required|unique:beritas,judul,' . $id,
            'deskripsi' => 'required',
            'foto' => 'nullable|image|mimes:png,jpg|max:2048',
            'id_kategori' => 'required|exists:kategoris,id',
            'tag' => 'nullable|array',
            'id_user' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Cari berita berdasarkan ID
            $berita = Berita::findOrFail($id);

            // Update data berita
            $berita->judul = $request->judul;
            $berita->deskripsi = $request->deskripsi;
            $berita->id_user = $request->id_user;
            $berita->id_kategori = $request->id_kategori;

            // Jika ada foto yang diupload, simpan foto baru
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('public/berita');
                $berita->foto = $path;
            }

            $berita->save();

            // Update tag berita jika ada
            if ($request->has('tag')) {
                $berita->tags()->sync($request->tag);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diperbarui',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $berita = Berita::findOrFail($id);
            $berita->tags()->detach(); // Perbaiki penggunaan metode relasi
            // Hapus foto
            Storage::delete($berita->foto);
            $berita->delete();
            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil dihapus',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan',
                'errors' => $e->getMessage(),
            ], 404);
        }
    }
}
