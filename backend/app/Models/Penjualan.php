<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pelanggan', 
        'id_metode_bayar', 
        'id_jenis_kirim', 
        'tgl_penjualan', 
        'url_resep', 
        'ongkos_kirim', 
        'biaya_app', 
        'total_bayar', 
        'status_order', 
        'keterangan_status'
    ];
}