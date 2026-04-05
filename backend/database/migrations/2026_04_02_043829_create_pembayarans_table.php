<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_penjualan')->constrained('penjualans')->onDelete('cascade');
        $table->dateTime('tgl_bayar');
        $table->double('jumlah_bayar');
        $table->string('bukti_bayar'); // Simpan nama file foto (misal: bukti_1.jpg)
        $table->enum('status_konfirmasi', ['Menunggu', 'Diterima', 'Ditolak'])->default('Menunggu');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
