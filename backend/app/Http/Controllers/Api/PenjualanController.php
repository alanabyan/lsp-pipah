<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Obat;

class PenjualanController extends Controller
{
   public function index()
    {
        return response()->json(Penjualan::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'id_metode_bayar' => 'required|exists:metode_bayar,id',
            'id_jenis_kirim' => 'required|exists:jenis_pengirimans,id',

        ]);

        $penjualan = Penjualan::create([
            'id_pelanggan' => $request->id_pelanggan,
            'id_metode_bayar' => $request->id_metode_bayar,
            'id_jenis_kirim' => $request->id_jenis_kirim,
            'tgl_penjualan' => now(),
            'total_bayar' => $request->total_bayar ?? 0,
            'status_order' => 'Menunggu Konfirmasi',
        ]);

        return response()->json([
            'message' => 'Nota Penjualan berhasil dibuat!',
            'data' => $penjualan
        ], 201);
    }

    // 3. Lihat detail satu nota penjualan
    public function show($id)
    {
        $penjualan = Penjualan::find($id);

        if (!$penjualan) {
            return response()->json(['message' => 'Nota penjualan tidak ditemukan'], 404);
        }

        return response()->json($penjualan);
    }

    // 4. Update data nota (Edit)
    public function update(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);

        if (!$penjualan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Validasi opsional (tergantung apa yang mau diedit)
        $request->validate([
            'id_pelanggan' => 'exists:pelanggan,id',
            'id_metode_bayar' => 'exists:metode_bayar,id',
            'id_jenis_kirim' => 'exists:jenis_pengirimans,id',
            'status_order' => 'in:Menunggu Konfirmasi,Diproses,Menunggu Kurir,Dibatalkan Pembeli,Dibatalkan Penjual,Bermasalah,Selesai'
        ]);

        $penjualan->update($request->all());

        return response()->json([
            'message' => 'Nota penjualan berhasil diperbarui!',
            'data' => $penjualan
        ]);
    }

    public function batalkanPesanan($id)
{
    try {
        // Cari penjualan beserta detailnya
        $penjualan = Penjualan::with('detailPenjualan')->findOrFail($id);

        // Loop untuk mengembalikan stok
        foreach ($penjualan->detailPenjualan as $detail) {
            $obat = Obat::find($detail->id_obat);
            if ($obat) {
                // Pastikan menggunakan 'jumlah_beli' sesuai tabel detail kamu
                $obat->stok += $detail->jumlah_beli;
                $obat->save();
            }
        }

        // Hapus detail lalu hapus data penjualannya
        $penjualan->detailPenjualan()->delete();
        $penjualan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dibatalkan dan stok dikembalikan!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal: ' . $e->getMessage()
        ], 500);
    }
}

    // 5. Hapus nota penjualan
    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);

        if (!$penjualan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $penjualan->delete();

        return response()->json([
            'message' => 'Nota penjualan berhasil dihapus!'
        ]);
    }
}
