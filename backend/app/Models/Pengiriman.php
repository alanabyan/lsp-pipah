<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengirimans';
    protected $fillable = [
        'id_penjualan', 
        'no_resi', 
        'nama_kurir', 
        'tgl_kirim', 
        'tgl_tiba', 
        'status_kirim', 
        'keterangan'
    ];
}