<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PelangganAuthController;
use App\Http\Controllers\Api\StaffAuthController; 
use App\Http\Controllers\Api\JenisObatController;
use App\Http\Controllers\Api\ObatController;
use App\Http\Controllers\Api\KeranjangController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\MetodePembayaranController;
use App\Http\Controllers\Api\JenisPengirimanController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\DetailPembelianController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\DetailPenjualanController;
use App\Http\Controllers\Api\PengirimanController;
use App\Http\Controllers\Api\PembayaranController;


Route::prefix('pelanggan')->group(function () {
    // Public Pelanggan
    Route::post('/register', [PelangganAuthController::class, 'register']);
    Route::post('/login', [PelangganAuthController::class, 'login']);

    // Auth Pelanggan (Hanya untuk pembeli)
    Route::middleware('auth:pelanggan')->group(function () {
        Route::get('/me', [PelangganAuthController::class, 'me']);
        Route::post('/logout', [PelangganAuthController::class, 'logout']);
        Route::get('/keranjang', [KeranjangController::class, 'index']);
        Route::post('/tambah-keranjang', [KeranjangController::class, 'store']);
        Route::post('/edit-keranjang/{id}', [KeranjangController::class, 'update']);
        Route::delete('/hapus-keranjang/{id}', [KeranjangController::class, 'destroy']);
        Route::post('/checkout-sekarang', [PenjualanController::class, 'store']);
        Route::post('/detail-penjualan', [DetailPenjualanController::class, 'store']);
        Route::post('/bayar', [PembayaranController::class, 'store']);
    });
});


// Login khusus internal (Admin, Apoteker, Kasir, dll)
Route::post('/staff/login', [StaffAuthController::class, 'login']);

    Route::get('/obat', [ObatController::class, 'index']);
    Route::get('/jenis-obat', [JenisObatController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/distributor', [DistributorController::class, 'index']);
    Route::get('/metode-bayar', [MetodePembayaranController::class, 'index']);
    Route::get('/jenis-pengiriman', [JenisPengirimanController::class, 'index']);


    Route::middleware('checkRole:admin,apoteker')->group(function () {

        Route::post('/jenis-obat', [JenisObatController::class, 'store']);
        Route::delete('/jenis-obat/{id}', [JenisObatController::class, 'destroy']);
        
        // Obat
        Route::post('/tambah-obat', [ObatController::class, 'store']);
        Route::post('/edit-obat/{id}', [ObatController::class, 'update']);
        Route::delete('/hapus-obat/{id}', [ObatController::class, 'destroy']);

        // Distributor
        Route::post('/distributor', [DistributorController::class, 'store']);
        Route::get('/distributor/{id}', [DistributorController::class, 'show']);
        Route::delete('/distributor/{id}', [DistributorController::class, 'destroy']);
        
        // Pembelian Stok
    });

    // Route yang boleh diakses Admin, Kasir, DAN Pelanggan (Hanya Riwayat & Detail)
Route::middleware('checkRole:admin,kasir,pelanggan')->group(function () {
    Route::get('/penjualan', [PenjualanController::class, 'index']);
    Route::get('/penjualan/{id}', [PenjualanController::class, 'show']);
});

// Route yang BENAR-BENAR KHUSUS Staff (Admin & Kasir)
Route::middleware('checkRole:admin,kasir')->group(function () {
    Route::post('/penjualan', [PenjualanController::class, 'store']); // Biasanya pelanggan pakai checkout-sekarang kan?
    Route::get('/pembayaran', [PembayaranController::class, 'index']);
    Route::post('/pembayaran-konfirmasi/{id}', [PembayaranController::class, 'updateStatus']);
    Route::get('/pembelian', [PembelianController::class, 'index']);
    Route::post('/pembelian', [PembelianController::class, 'store']);
    Route::get('/pembelian/{id}', [PembelianController::class, 'show']);
    Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy']);
});

    // --- D. KHUSUS KARYAWAN/KURIR (Logistik) ---
    Route::middleware('checkRole:admin,karyawan')->group(function () {
        Route::get('/pengiriman', [PengirimanController::class, 'index']);
        Route::post('/pengiriman', [PengirimanController::class, 'store']);
        Route::put('/pengiriman-update/{id}', [PengirimanController::class, 'update']);
        
        Route::post('/jenis-pengiriman', [JenisPengirimanController::class, 'store']);
        Route::delete('/jenis-pengiriman/{id}', [JenisPengirimanController::class, 'destroy']);
    });

    // --- E. KHUSUS PEMILIK (Laporan Keuangan) ---
    Route::middleware('checkRole:admin,pemilik')->group(function () {
        Route::get('/laporan-pembelian', [PembelianController::class, 'index']);
        Route::get('/laporan-penjualan', [PenjualanController::class, 'index']);
    });
});