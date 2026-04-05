<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller; 
use App\Models\JenisPengiriman;
use Illuminate\Http\Request;

class JenisPengirimanController extends Controller
{
    // 1. Ambil semua data (Liat Daftar)
    public function index()
    {
        $data = JenisPengiriman::all();
        return response()->json($data);
    }

    // 2. Simpan data baru (Tambah)
    public function store(Request $request)
    {
        $request->validate([
            'jenis_kirim' => 'required|in:ekonomi,kargo,regular,same day,standar',
            'nama_ekspedisi' => 'required',
            'logo_ekspedisi' => 'required',
        ]);

        $data = JenisPengiriman::create($request->all());

        return response()->json([
            'message' => 'Jenis pengiriman berhasil ditambahkan!',
            'data' => $data
        ], 201);
    }

    // 3. Lihat detail satu data
    public function show($id)
    {
        $data = JenisPengiriman::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($data);
    }

    // 4. Hapus data
    public function destroy($id)
    {
        $data = JenisPengiriman::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}