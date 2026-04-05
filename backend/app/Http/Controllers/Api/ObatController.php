<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ObatController extends Controller
{
    public function index()
    {
        return response()->json(Obat::orderBy('nama_obat')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'idjenis' => ['required', 'integer', 'exists:jenis_obat,id'],
            'nama_obat' => ['required', 'string', 'max:150'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'deskripsi_obat' => ['nullable', 'string'],
            'foto1' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'foto2' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'foto3' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $data = $request->all();


        for ($i = 1; $i <= 3; $i++) {
            $key = 'foto' . $i;
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $nama_file = 'obat_' . $i . '_' . str_replace(' ', '_', strtolower($request->nama_obat)) . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                $file->storeAs('obat', $nama_file, 'public');
                $data[$key] = $nama_file;
            }
        }

        $obat = Obat::create($data);

        return response()->json([
            'message' => 'Obat berhasil dibuat',
            'data' => $obat,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $obat = Obat::find($id);
        if (!$obat) {
            return response()->json(['message' => 'Obat tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_obat' => ['sometimes', 'string', 'max:150'],
            'harga_jual' => ['sometimes', 'integer', 'min:0'],
            'foto1' => ['sometimes', 'nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'foto2' => ['sometimes', 'nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'foto3' => ['sometimes', 'nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'stok' => ['sometimes', 'integer', 'min:0'],
        ]);

        $data = $request->all();

        for ($i = 1; $i <= 3; $i++) {
            $key = 'foto' . $i;
            if ($request->hasFile($key)) {
                // Hapus foto lama kalau ada
                if ($obat->$key) {
                    Storage::delete('public/obat/' . $obat->$key);
                }

                $file = $request->file($key);
                $nama_file = 'obat_' . $i . '_' . str_replace(' ', '_', strtolower($obat->nama_obat)) . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                $file->storeAs('obat', $nama_file, 'public');
                $data[$key] = $nama_file;
            }
        }

        $obat->update($data);

        return response()->json([
            'message' => 'Obat berhasil diupdate',
            'data' => $obat
        ]);
    }

    public function destroy($id)
    {
        $obat = Obat::find($id);
        if (!$obat) {
            return response()->json(['message' => 'Obat tidak ditemukan'], 404);
        }

        // Hapus semua foto di folder sebelum data dihapus
        for ($i = 1; $i <= 3; $i++) {
            $key = 'foto' . $i;
            if ($obat->$key) {
                Storage::delete('public/obat/' . $obat->$key);
            }
        }

        $obat->delete();
        return response()->json(['message' => 'Obat berhasil dihapus']);
    }
}