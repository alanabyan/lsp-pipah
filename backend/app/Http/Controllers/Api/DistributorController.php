<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    // Menampilkan semua data distributor (bisa untuk API atau View)
    public function index()
    {
        $distributor = Distributor::all();
        return response()->json($distributor); 
    }

    // Fungsi simpan data
    public function store(Request $request)
    {
        $request->validate([
            'nama_distributor' => 'required',
            'telepon' => 'required',
            'alamat' => 'required',
        ]);

        $data = Distributor::create($request->all());
        return response()->json(['message' => 'Data Berhasil Disimpan', 'data' => $data]);
    }
    public function show($id)
    {
        $distributor = Distributor::find($id);

        if (!$distributor) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($distributor);
    }

    // Fungsi menghapus data
    public function destroy($id)
    {
        $distributor = Distributor::find($id);

        if (!$distributor) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $distributor->delete();

        return response()->json(['message' => 'Data Berhasil Dihapus']);
    }
}

