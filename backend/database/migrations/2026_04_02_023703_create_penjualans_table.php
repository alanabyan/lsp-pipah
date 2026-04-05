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
        Schema::create('penjualans', function (Blueprint $table) {
        $table->id();
        // Foreign Key sesuai ERD
        $table->foreignId('id_pelanggan')->constrained('pelanggan'); 
        $table->foreignId('id_metode_bayar')->constrained('metode_bayar'); 
        $table->foreignId('id_jenis_kirim')->constrained('jenis_pengirimans');

        $table->date('tgl_penjualan');
        $table->string('url_resep')->nullable(); 
        $table->double('ongkos_kirim')->default(0);
        $table->double('biaya_app')->default(0);
        $table->double('total_bayar')->default(0);

        $table->enum('status_order', [
            'Menunggu Konfirmasi', 'Diproses', 'Menunggu Kurir', 
            'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah', 'Selesai'
        ])->default('Menunggu Konfirmasi');

        $table->text('keterangan_status')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
