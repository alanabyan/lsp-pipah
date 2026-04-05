<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPembelian;
use App\Models\Obat; // Wajib panggil model Obat untuk update stok
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk keamanan database (Transaction)

class DetailPembelianController extends Controller
{
    // 1. Simpan Detail & Update Stok Obat
    public function store(Request $request)
    {
        $request->validate([
            'id_pembelian' => 'required|exists:pembelians,id',
            'id_obat' => 'required|exists:obat,id',
            'jumlah' => 'required|integer|min:1',
            'harga_beli' => 'required|numeric',
        ]);

        // Gunakan DB Transaction supaya kalau stok gagal update, detail gagal simpan (biar sinkron)
        DB::beginTransaction();

        try {
            $subtotal = $request->jumlah * $request->harga_beli;

            // Simpan ke tabel detail_pembelians
            $detail = DetailPembelian::create([
                'id_pembelian' => $request->id_pembelian,
                'id_obat' => $request->id_obat,
                'jumlah' => $request->jumlah,
                'harga_beli' => $request->harga_beli,
                'subtotal' => $subtotal
            ]);

            // LOGIKA UPDATE STOK: Ambil data obat, lalu tambahkan stoknya
            $obat = Obat::find($request->id_obat);
            $obat->stok = $obat->stok + $request->jumlah;
            $obat->save();

            DB::commit(); // Selesaikan transaksi jika semua oke

            return response()->json([
                'message' => 'Obat berhasil ditambahkan dan STOK BERTAMBAH!',
                'data' => $detail
            ], 201);

        } catch (\Exception $e) {
            DB::rollback(); // Batalkan semua jika ada error
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }

    // 2. Lihat isi satu Nota (semua obat di nota itu)
    public function showByNota($id_pembelian)
    {
        $data = DetailPembelian::with('obat')
                ->where('id_pembelian', $id_pembelian)
                ->get();
                
        return response()->json($data);
    }

    // 3. Hapus Item (Stok Obat akan berkurang kembali)
    public function destroy($id)
    {
        $detail = DetailPembelian::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            // Sebelum dihapus, kurangi dulu stok obatnya (karena batal beli)
            $obat = Obat::find($detail->id_obat);
            $obat->stok = $obat->stok - $detail->jumlah;
            $obat->save();

            $detail->delete();

            DB::commit();
            return response()->json(['message' => 'Item dihapus dan STOK DIKEMBALIKAN']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }
}