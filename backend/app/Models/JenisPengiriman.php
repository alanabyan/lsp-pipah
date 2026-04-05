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
}