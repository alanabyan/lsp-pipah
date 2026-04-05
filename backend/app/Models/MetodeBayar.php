<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodeBayar extends Model
{

    protected $table = 'metode_bayar';

    protected $fillable = [
        'metode_pembayaran',
        'tempat_bayar',
        'no_rekening',
        'url_logo'
    ];

    protected $appends = ['full_url_logo'];


    public function getFullUrlLogoAttribute()
    {

        if ($this->url_logo) {
            return asset('storage/metode_bayar/' . $this->url_logo);
        }
        

        return null;
    }
}

