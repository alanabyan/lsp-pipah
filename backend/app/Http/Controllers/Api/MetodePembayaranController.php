<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetodeBayar; // Pastikan ini sesuai nama File Model kamu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MetodePembayaranController extends Controller
{
    public function index()
    {

        return response()->json(MetodeBayar::orderBy('metode_pembayaran')->get());
    }

    public function store(Request $request)
    {

        $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:30'],
            'tempat_bayar' => ['required', 'string', 'max:50'],
            'no_rekening' => ['required', 'string', 'max:25'],
            'url_logo' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:1024'],
        ]);

        $data = $request->all();

 
        if ($request->hasFile('url_logo')) {
            $file = $request->file('url_logo');
            

            $nama_file = 'logo_' . str_replace(' ', '_', strtolower($request->metode_pembayaran)) . '_' . time() . '.' . $file->getClientOriginalExtension();
            

            $file->storeAs('metode_bayar', $nama_file, 'public');
            

            $data['url_logo'] = $nama_file;
        }

        $metode = MetodeBayar::create($data);

        return response()->json([
            'message' => 'Metode pembayaran berhasil dibuat',
            'data' => $metode
        ], 201);
    }

    public function destroy($id)
    {
        $metode = MetodeBayar::find($id);

        if (!$metode) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // TAMBAHAN: Hapus file foto di folder biar nggak menuh-menuhin memori
        if ($metode->url_logo) {
            Storage::delete('public/metode_bayar/' . $metode->url_logo);
        }

        $metode->delete();

        return response()->json(['message' => 'Metode bayar dihapus']);
    }
}