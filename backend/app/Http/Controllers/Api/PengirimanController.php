<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    // Lihat semua data pengiriman
    public function index()
    {
        return response()->json(Pengiriman::all());
    }

    // Input data pengiriman baru (Admin)
    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|exists:penjualans,id',
            'no_resi' => 'required|unique:pengirimans,no_resi',
            'nama_kurir' => 'required|string',
            'tgl_kirim' => 'required|date',
        ]);

        $pengiriman = Pengiriman::create([
            'id_penjualan' => $request->id_penjualan,
            'no_resi' => $request->no_resi,
            'nama_kurir' => $request->nama_kurir,
            'tgl_kirim' => $request->tgl_kirim,
            'status_kirim' => 'Dikirim', // Otomatis set jadi dikirim
        ]);

        return response()->json([
            'message' => 'Data pengiriman berhasil diinput!',
            'data' => $pengiriman
        ], 201);
    }

    // Update status (Misal: Barang sudah Diterima)
    public function update(Request $request, $id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        
        $pengiriman->update([
            'status_kirim' => $request->status_kirim, // Proses / Dikirim / Diterima
            'tgl_tiba' => $request->status_kirim == 'Diterima' ? now() : null,
        ]);

        return response()->json([
            'message' => 'Status pengiriman diperbarui!',
            'data' => $pengiriman
        ]);
    }

    public function destroy($id)
    {
        Pengiriman::findOrFail($id)->delete();
        return response()->json(['message' => 'Data pengiriman dihapus']);
    }
}