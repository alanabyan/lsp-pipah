<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = ['kode_masuk', 'tanggal_masuk', 'id_distributor', 'total_item', 'total_harga'];


    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'id_distributor');
    }
}