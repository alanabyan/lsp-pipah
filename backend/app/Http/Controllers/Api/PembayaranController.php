<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    // 1. Ambil semua data pembayaran (untuk Admin)
    public function index()
    {
        $pembayaran = Pembayaran::with('penjualan')->get();
        return response()->json($pembayaran);
    }

    // 2. Upload Bukti Bayar (Pelanggan)
    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|exists:penjualans,id',
            'jumlah_bayar' => 'required|numeric',
            'bukti_bayar'  => 'required|image|mimes:jpg,png,jpeg|max:2048', // Max 2MB
        ]);

        // Proses Simpan Gambar
        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            // Buat nama file unik: idpenjualan_timestamp.ext
            $nama_file = $request->id_penjualan . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke folder: storage/app/public/bukti_pembayaran
            $file->storeAs('public/bukti_pembayaran', $nama_file);
        }

        $pembayaran = Pembayaran::create([
            'id_penjualan' => $request->id_penjualan,
            'tgl_bayar' => now(),
            'jumlah_bayar' => $request->jumlah_bayar,
            'bukti_bayar' => $nama_file,
            'status_konfirmasi' => 'Menunggu',
        ]);

        return response()->json([
            'message' => 'Bukti bayar berhasil diupload! Menunggu konfirmasi admin.',
            'data' => $pembayaran
        ], 201);
    }

    // 3. Konfirmasi Pembayaran (Admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_konfirmasi' => 'required|in:Menunggu,Diterima,Ditolak',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update([
            'status_konfirmasi' => $request->status_konfirmasi
        ]);

        return response()->json([
            'message' => 'Status pembayaran diperbarui menjadi: ' . $request->status_konfirmasi,
            'data' => $pembayaran
        ]);
    }
}