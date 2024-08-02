<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\str;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::with('kategori', 'tag', 'user',)->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'daftar berita',
            'data' => $berita,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|unique:beritas',
            'deskripsi' => 'required',
            'foto' => 'required|image|mimes:png,jpg|max:2048',
            'id_kategori' => 'required',
            'tag' => 'required|array',
            'id_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // upload foto
            $path = $request->file('foto')->store('berita');

            $berita = new Berita;
            $berita->judul = $request->judul;
            $berita->slug = Str::slug($request->judul);
            $berita->deskripsi = $request->deskripsi;
            $berita->foto = $path;
            $berita->id_user = $request->id_user;
            $berita->id_kategori = $request->id_kategori;
            $berita->save();

            // melampirkan banyak tag
            $berita->tag()->attach($request->tag);
            return response()->json([
                'success' => true,
                'message' => 'berita berhasil dibuat',
                'data' => $berita,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'terjadi kesalahan',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $berita = Berita::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'detail berita',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'data tidak ditemukan',
                'errors' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'judul' => 'required',
        'deskripsi' => 'required',
        'foto' => 'nullable|image|mimes:png,jpg|max:2048',
        'id_kategori' => 'required',
        'tag' => 'required|array',
        'id_user' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'validasi gagal',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $berita = Berita::findOrFail($id);

        // upload foto if provided
        if ($request->hasFile('foto')) {
            // delete the old photo
            Storage::delete($berita->foto);

            // upload the new photo
            $path = $request->file('foto')->store('berita');
            $berita->foto = $path;
        }

        $berita->judul = $request->judul;
        $berita->slug = Str::slug($request->judul);
        $berita->deskripsi = $request->deskripsi;
        $berita->id_user = $request->id_user;
        $berita->id_kategori = $request->id_kategori;
        $berita->save();

        // update tags
        $berita->tag()->sync($request->tag);

        return response()->json([
            'success' => true,
            'message' => 'berita berhasil diupdate',
            'data' => $berita,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'terjadi kesalahan',
            'errors' => $e->getMessage(),
        ], 500);
    }
}
 public function destroy(string $id)
 {
    try {
        $berita = Berita::findOrFail($id);
        //hapus berita
        $berita->tag()->detach();
        //hapus foto
        Storage::delete($berita->foto);
        $berita->delete();
        return response()->json([
            'success' => true,
            'message' => 'berita' . $berita->judul . 'berhasil dihapus',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'data tidak ditemukan',
            'errors' => $e->getMessage(),
        ], 404);
    }

 }
}
