<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller; 
use App\Models\JenisPengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Tambahkan ini untuk urusan hapus file nanti

class JenisPengirimanController extends Controller
{
    public function index()
    {
        $data = JenisPengiriman::all();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_kirim' => 'required|in:ekonomi,kargo,regular,same day,standar',
            'nama_ekspedisi' => 'required',
            'logo_ekspedisi' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        $data = $request->only(['jenis_kirim', 'nama_ekspedisi']);


        if ($request->hasFile('logo_ekspedisi')) {
            $file = $request->file('logo_ekspedisi');
            

            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

            $file->move(public_path('storage/jenis_pengiriman'), $filename);
            

            $data['logo_ekspedisi'] = $filename;
        }

        $result = JenisPengiriman::create($data);

        return response()->json([
            'message' => 'Jenis pengiriman berhasil ditambahkan!',
            'data' => $result
        ], 201);
    }

    public function show($id)
    {
        $data = JenisPengiriman::find($id);
        if (!$data) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        return response()->json($data);
    }

    public function destroy($id)
{
    $data = JenisPengiriman::find($id);

    if (!$data) {
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    // Ubah bagian ini supaya hapusnya sinkron dengan cara simpannya
    if ($data->logo_ekspedisi) {
        $path = public_path('storage/jenis_pengiriman/' . $data->logo_ekspedisi);
        if (file_exists($path)) {
            unlink($path); // Menghapus file fisik di public/storage
        }
    }

    $data->delete();

    return response()->json(['message' => 'Data berhasil dihapus']);
}
}