<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class JenisPengiriman extends Model
{
    use HasFactory; // Baris ini memanggil "fitur" di atas

    protected $table = 'jenis_pengirimans';

    protected $fillable = [
        'jenis_kirim', 
        'nama_ekspedisi', 
        'logo_ekspedisi'
    ];
    protected $appends = ['full_url_logo'];

    // Ini fungsi untuk menyusun URL lengkapnya
    public function getFullUrlLogoAttribute()
    {
        if ($this->logo_ekspedisi) {
            return asset('storage/jenis_pengiriman/' . $this->logo_ekspedisi);
        }
        return null;
    }
}