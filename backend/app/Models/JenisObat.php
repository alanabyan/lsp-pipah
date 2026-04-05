<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisObat extends Model
{
    protected $table = 'jenis_obat';

    protected $fillable = [
        'jenis',
        'deskripsi_jenis',
        'image_url',
    ];

    // --- TAMBAHKAN INI DI BAWAH FILLABLE ---

    // 1. Biar variabel 'url_lambang' otomatis muncul di hasil API/JSON
    protected $appends = ['url_lambang'];

    // 2. Fungsi buat bikin link foto otomatis
    public function getUrlLambangAttribute()
    {
        if ($this->image_url) {
            // Ini akan jadi: http://localhost:8000/storage/jenis_obat/nama_file.png
            return asset('storage/jenis_obat/' . $this->image_url);
        }
        
        return null; 
    }
}