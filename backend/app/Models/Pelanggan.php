<?php

namespace App\Models;

// WAJIB: Gunakan Authenticatable, bukan Model biasa agar bisa Login
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pelanggan extends Authenticatable
{
    // HasApiTokens: Agar bisa mengeluarkan token Sanctum
    // Notifiable: Agar bisa mengirim notifikasi (opsional tapi disarankan)
    use HasApiTokens, Notifiable;

    /**
     * Nama tabel di database
     */
    protected $table = 'pelanggan';

    /**
     * Kolom yang boleh diisi secara mass-assignment
     */
    protected $fillable = [
        'nama_pelanggan',
        'email',
        'kata_kunci',
        'no_telp',
        'alamat1', 'kota1', 'propinsi1', 'kodepos1',
        'alamat2', 'kota2', 'propinsi2', 'kodepos2',
        'alamat3', 'kota3', 'propinsi3', 'kodepos3',
        'foto',
        'url_ktp'
    ];

    /**
     * Kolom yang disembunyikan saat data diubah jadi JSON (API)
     */
    protected $hidden = [
        'kata_kunci',
        'remember_token',
    ];

    /**
     * Beritahu Laravel kalau kolom password kamu namanya 'kata_kunci'
     * Secara default Laravel mencari kolom bernama 'password'
     */
    public function getAuthPassword()
    {
        return $this->kata_kunci;
    }

    /**
     * Casting tipe data (opsional)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}