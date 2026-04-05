<?php

namespace App\Models;

use App\Models\JenisObat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obat extends Model
{
    use SoftDeletes;

    protected $table = 'obat';

    protected $fillable = [
        'idjenis',
        'nama_obat',
        'harga_jual',
        'deskripsi_obat',
        'foto1',
        'foto2',
        'foto3',
        'stok',
    ];

    // Ini supaya url_foto otomatis muncul di JSON
    protected $appends = ['url_foto1', 'url_foto2', 'url_foto3'];

    public function jenis()
    {
        return $this->belongsTo(JenisObat::class, 'idjenis');
    }


    public function getUrlFoto1Attribute()
    {
        return $this->foto1 ? asset('storage/obat/' . $this->foto1) : null;
    }

    public function getUrlFoto2Attribute()
    {
        return $this->foto2 ? asset('storage/obat/' . $this->foto2) : null;
    }

    public function getUrlFoto3Attribute()
    {
        return $this->foto3 ? asset('storage/obat/' . $this->foto3) : null;
    }
}