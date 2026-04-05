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
        Schema::create('pengirimans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_penjualan')->constrained('penjualans')->onDelete('cascade');
        $table->string('no_resi')->unique();
        $table->dateTime('tgl_kirim');
        $table->dateTime('tgl_tiba')->nullable();
        $table->enum('status_kirim', ['Proses', 'Dikirim', 'Diterima'])->default('Proses');
        $table->string('nama_kurir');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengirimen');
    }
};
