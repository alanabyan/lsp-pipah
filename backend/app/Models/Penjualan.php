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

    /**
     * Relasi ke Detail Penjualan
     * Ini yang bikin 'with(detailPenjualan)' di Controller jadi jalan.
     */
    public function detailPenjualan()
    {
        // 'id_penjualan' adalah kolom foreign key yang ada di tabel detail_penjualans
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan');
    }

    /**
     * Relasi ke Pelanggan (Optional tapi berguna)
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
}