<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Tambahin ini buat hapus file nanti

class JenisObatController extends Controller
{
    public function index()
    {
        return response()->json(JenisObat::orderBy('jenis')->get());
    }

    public function store(Request $request)
    {

        $request->validate([
            'jenis' => ['required', 'string', 'max:150'],
            'deskripsi_jenis' => ['nullable', 'string'],
            'image_url' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:1024'],
        ]);

        $data = $request->all();

        if ($request->hasFile('image_url')) {
            $file = $request->file('image_url');

            $nama_file = 'lambang_' . str_replace(' ', '_', strtolower($request->jenis)) . '_' . time() . '.' . $file->getClientOriginalExtension();

            $file->storeAs('jenis_obat', $nama_file, 'public');
            

            $data['image_url'] = $nama_file;
        }

        $jenis = JenisObat::create($data);

        return response()->json([
            'message' => 'Jenis obat berhasil dibuat',
            'data' => $jenis,
        ], 201);
    }

    public function destroy($id)
    {
        $jenis = JenisObat::find($id);

        if (!$jenis) {
            return response()->json(['message' => 'Jenis obat tidak ditemukan'], 404);
        }

   
        if ($jenis->image_url) {
            Storage::delete('public/jenis_obat/' . $jenis->image_url);
        }

        $jenis->delete();

        return response()->json(['message' => 'Jenis obat berhasil dihapus']);
    }
}