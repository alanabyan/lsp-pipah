<?php

namespace App\Models;
use App\Models\Pelanggan;
use App\Models\Obat;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $fillable =[
        'id_pelanggan',
        'id_obat',
        'jumlah_order',
        'harga',
        'subtotal'
    ];
     public function pelanggan()
    {
        return $this->belongsTo(pelanggan::class, 'id_pelanggan');
    }

     public function obat()
    {
        return $this->belongsTo(DataBarang::class, 'barang_id');
    }


}
