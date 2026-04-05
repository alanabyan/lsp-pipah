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
        Schema::create('pembelians', function (Blueprint $table) {
        $table->id();
        $table->string('kode_masuk', 50); // Contoh: PMB-001
        $table->date('tanggal_masuk');
        
        // Relasi ke tabel distributor
        $table->unsignedBigInteger('id_distributor');
        $table->foreign('id_distributor')->references('id')->on('distributors')->onDelete('cascade');
        
        $table->integer('total_item');
        $table->decimal('total_harga', 15, 2);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
