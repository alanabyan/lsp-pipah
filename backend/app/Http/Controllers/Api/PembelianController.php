<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index()
    {
        // with('distributor') biar nama distributormya muncul, bukan cuma ID-nya
        $data = Pembelian::with('distributor')->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_masuk' => 'required|unique:pembelians',
            'tanggal_masuk' => 'required|date',
            'id_distributor' => 'required|exists:distributors,id',
            'total_item' => 'required|integer',
            'total_harga' => 'required|numeric',
        ]);

        $pembelian = Pembelian::create($request->all());
        return response()->json(['message' => 'Transaksi pembelian berhasil', 'data' => $pembelian]);
    }
    // Fungsi untuk melihat detail satu transaksi
    public function show($id)
    {
        $pembelian = Pembelian::with('distributor')->find($id);

        if (!$pembelian) {
            return response()->json(['message' => 'Data pembelian tidak ditemukan'], 404);
        }

        return response()->json($pembelian);
    }

    // Fungsi untuk EDIT data
    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::find($id);

        if (!$pembelian) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            // 'unique:pembelians,kode_masuk,'.$id artinya: 
            // Boleh pakai kode yang sama kalau itu milik data ini sendiri (saat update)
            'kode_masuk' => 'required|unique:pembelians,kode_masuk,'.$id,
            'tanggal_masuk' => 'required|date',
            'id_distributor' => 'required|exists:distributors,id',
            'total_item' => 'required|integer',
            'total_harga' => 'required|numeric',
        ]);

        $pembelian->update($request->all());

        return response()->json([
            'message' => 'Transaksi pembelian berhasil diperbarui',
            'data' => $pembelian
        ]);
    }

    // Fungsi untuk HAPUS data
    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);

        if (!$pembelian) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $pembelian->delete();

        return response()->json(['message' => 'Transaksi pembelian berhasil dihapus']);
    }
    
}