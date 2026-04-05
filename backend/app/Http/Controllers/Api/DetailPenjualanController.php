<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPenjualan;
use App\Models\Obat; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailPenjualanController extends Controller
{
    public function index()
    {
        return response()->json(DetailPenjualan::all());
    }

    public function store(Request $request)
    {

        $request->validate([
            'id_penjualan' => 'required|exists:penjualans,id',
            'id_obat' => 'required|exists:obat,id',
            'jumlah_beli' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $obat = Obat::find($request->id_obat);


            if ($obat->stok < $request->jumlah_beli) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stok kurang! Sisa cuma: ' . $obat->stok
                ], 400);
            }


            $subtotal = $request->jumlah_beli * $obat->harga_jual;

            $detail = DetailPenjualan::create([
                'id_penjualan' => $request->id_penjualan,
                'id_obat' => $request->id_obat,
                'jumlah_beli' => $request->jumlah_beli,
                'harga_beli' => $obat->harga_jual,
                'subtotal' => $subtotal,
            ]);


            $obat->stok -= $request->jumlah_beli;
            $obat->save();

            return response()->json([
                'status' => true,
                'message' => 'Barang masuk keranjang & stok berkurang!',
                'data' => $detail
            ], 201);
        });
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'jumlah_beli' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $detail = DetailPenjualan::findOrFail($id);
            $obat = Obat::find($detail->id_obat);

            $obat->stok += $detail->jumlah_beli;

            if ($obat->stok < $request->jumlah_beli) {
                return response()->json(['message' => 'Stok tidak cukup untuk perubahan ini!'], 400);
            }

            $obat->stok -= $request->jumlah_beli;
            $obat->save();

            $detail->update([
                'jumlah_beli' => $request->jumlah_beli,
                'subtotal' => $request->jumlah_beli * $detail->harga_beli,
            ]);

            return response()->json(['message' => 'Berhasil update jumlah!', 'data' => $detail]);
        });
    }


    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $detail = DetailPenjualan::findOrFail($id);
            $obat = Obat::find($detail->id_obat);


            $obat->stok += $detail->jumlah_beli;
            $obat->save();

            $detail->delete();

            return response()->json(['message' => 'Barang dihapus dari nota, stok dikembalikan!']);
        });
    }
}