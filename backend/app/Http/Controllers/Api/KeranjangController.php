<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Obat;

class KeranjangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user('pelanggan');

            return response()->json([
                'user' => $user,
                'keranjang' => Keranjang::with('obat')
                    ->where('id_pelanggan', $user?->id)
                    ->get()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'id_obat' => ['required','exists:obat,id'],
        'jumlah_order' => ['required','integer','min:1']
    ]);

    $pelangganId = $request->user('pelanggan')->id;

    $obat = Obat::find($data['id_obat']);

    $item = Keranjang::where('id_pelanggan',$pelangganId)
        ->where('id_obat',$data['id_obat'])
        ->first();

    if($item){

        $item->jumlah_order += $data['jumlah_order'];
        $item->subtotal = $item->jumlah_order * $item->harga;
        $item->save();

    }else{

        $item = Keranjang::create([
            'id_pelanggan'=>$pelangganId,
            'id_obat'=>$data['id_obat'],
            'jumlah_order'=>$data['jumlah_order'],
            'harga'=>$obat->harga_jual,
            'subtotal'=>$data['jumlah_order'] * $obat->harga_jual
        ]);

    }

    return response()->json($item);
}

public function update(Request $request,$id)
{
    $data = $request->validate([
        'jumlah_order'=>['required','integer','min:1']
    ]);

    $item = Keranjang::find($id);

    if(!$item){
        return response()->json(['message'=>'Item tidak ditemukan'],404);
    }

    $item->jumlah_order = $data['jumlah_order'];
    $item->subtotal = $data['jumlah_order'] * $item->harga;
    $item->save();

    return response()->json($item);
}

public function destroy($id)
{
    Keranjang::find($id)?->delete();

    return response()->json([
        'message'=>'Item keranjang dihapus'
    ]);
}

}
