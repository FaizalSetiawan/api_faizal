<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::latest()->get(); // Mengambil daftar pengguna terbaru

        return response()->json([
            'success' => true,
            'message' => 'Daftar Pengguna',
            'data' => $users,
        ], 200);
    }

    /**
     * Menyimpan pengguna baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8',
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
            // Simpan data pengguna
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil dibuat',
                'data' => $user,
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
     * Menampilkan detail pengguna berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Cari pengguna berdasarkan ID
        $user = User::find($id);

        // Jika pengguna ditemukan
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Pengguna',
                'data' => $user,
            ], 200);
        }

        // Jika pengguna tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan',
        ], 404);
    }

    /**
     * Memperbarui pengguna yang ada.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'min:2',
                'unique:users,name,' . $id,
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $id,
            ],
            'password' => 'nullable|min:8', // Password optional pada update
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
            // Cari dan perbarui pengguna berdasarkan ID
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->has('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diperbarui',
                'data' => $user,
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
     * Menghapus pengguna berdasarkan ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
{
    try {
        // Cari pengguna berdasarkan ID
        $user = User::findOrFail($id);

        // Simpan informasi pengguna yang akan dihapus untuk digunakan dalam respons
        $userData = $user->toArray();

        // Hapus pengguna
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil dihapus',
            'data' => $userData, // Sertakan informasi pengguna yang dihapus
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan',
        ], 404);
    }
}
}
