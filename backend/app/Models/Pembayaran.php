<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    // Menentukan nama tabel karena kita pakai akhiran 's' (pembayarans)
    protected $table = 'pembayarans';

    // Daftar kolom yang boleh diisi manual
    protected $fillable = [
        'id_penjualan',
        'tgl_bayar',
        'jumlah_bayar',
        'bukti_bayar',
        'status_konfirmasi'
    ];

    /**
     * Relasi: Satu pembayaran dimiliki oleh satu penjualan (Nota)
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }

    /**
     * Accessor: Supaya pas manggil API, link fotonya otomatis lengkap
     * Jadi di Vue tinggal panggil: item.url_bukti
     */
    protected $appends = ['url_bukti'];

    public function getUrlBuktiAttribute()
    {
        if ($this->bukti_bayar) {
            return asset('storage/bukti_pembayaran/' . $this->bukti_bayar);
        }
        return null;
    }
}