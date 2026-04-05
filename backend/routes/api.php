<?php
use App\Http\Controllers\Api\PelangganAuthController;
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
use Illuminate\Support\Facades\Route;

Route::prefix('pelanggan')->group(function () {
    Route::post('/register', [PelangganAuthController::class, 'register']);
    Route::post('/login', [PelangganAuthController::class, 'login']);

    Route::middleware('auth:pelanggan')->group(function () {
        Route::get('/me', [PelangganAuthController::class, 'me']);
        Route::post('/logout', [PelangganAuthController::class, 'logout']);
        Route::get('/keranjang', [KeranjangController::class, 'index']);
        Route::post('/tambah-keranjang', [KeranjangController::class, 'store']);
        Route::post('/edit-keranjang/{id}', [KeranjangController::class, 'update']);
        Route::delete('/hapus-keranjang/{id}', [KeranjangController::class, 'destroy']);
    });
});


Route::get('/jenis-obat', [JenisObatController::class, 'index']);
Route::post('/jenis-obat', [JenisObatController::class, 'store']);
Route::delete('/jenis-obat/{id}', [JenisObatController::class, 'destroy']);

Route::get('/obat', [ObatController::class, 'index']);
Route::post('/tambah-obat', [ObatController::class, 'store']);
Route::post('/edit-obat/{id}', [ObatController::class, 'update']);
Route::delete('/hapus-obat/{id}', [ObatController::class, 'destroy']);

Route::get('/metode-bayar', [MetodePembayaranController::class, 'index']);
Route::post('/metode-bayar', [MetodePembayaranController::class, 'store']);
Route::delete('/metode-bayar/{id}', [MetodePembayaranController::class, 'destroy']);

Route::get('/distributor', [DistributorController::class, 'index']);   
Route::get('/distributor/{id}', [DistributorController::class, 'show']);  
Route::post('/distributor', [DistributorController::class, 'store']);     
Route::delete('/distributor/{id}', [DistributorController::class, 'destroy']); 

Route::get('/jenis-pengiriman', [JenisPengirimanController::class, 'index']);
Route::post('/jenis-pengiriman', [JenisPengirimanController::class, 'store']);
Route::get('/jenis-pengiriman/{id}', [JenisPengirimanController::class, 'show']);
Route::delete('/jenis-pengiriman/{id}', [JenisPengirimanController::class, 'destroy']);

Route::get('/pembelian', [PembelianController::class, 'index']);
Route::post('/pembelian', [PembelianController::class, 'store']);
Route::get('/pembelian/{id}', [PembelianController::class, 'show']);
Route::put('/pembelian/{id}', [PembelianController::class, 'update']); 
Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy']); 

Route::post('/detail-pembelian', [DetailPembelianController::class, 'store']);
Route::get('/detail-pembelian/nota/{id_pembelian}', [DetailPembelianController::class, 'showByNota']);
Route::delete('/detail-pembelian/{id}', [DetailPembelianController::class, 'destroy']);

Route::get('/penjualan', [PenjualanController::class, 'index']);
Route::post('/penjualan', [PenjualanController::class, 'store']);
Route::get('/penjualan/{id}', [PenjualanController::class, 'show']);
Route::put('/penjualan/{id}', [PenjualanController::class, 'update']); 
Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy']);

Route::get('/detail-penjualan', [DetailPenjualanController::class, 'index']);
Route::post('/detail-penjualan', [DetailPenjualanController::class, 'store']);
Route::post('/detail-penjualan/{id}', [DetailPenjualanController::class, 'update']); 
Route::delete('/detail-penjualan/{id}', [DetailPenjualanController::class, 'destroy']);

Route::get('/pengiriman', [PengirimanController::class, 'index']);
Route::post('/pengiriman', [PengirimanController::class, 'store']);
Route::post('/pengiriman-update/{id}', [PengirimanController::class, 'update']);
Route::delete('/pengiriman/{id}', [PengirimanController::class, 'destroy']);

Route::get('/pembayaran', [PembayaranController::class, 'index']);
Route::post('/pembayaran', [PembayaranController::class, 'store']);
Route::post('/pembayaran-konfirmasi/{id}', [PembayaranController::class, 'updateStatus']);