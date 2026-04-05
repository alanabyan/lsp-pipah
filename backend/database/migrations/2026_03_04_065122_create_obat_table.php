<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idjenis')->constrained('jenis_obat');

            $table->string('nama_obat',150);
            $table->integer('harga_jual');
            $table->text('deskripsi_obat')->nullable();

            $table->string('foto1')->nullable();
            $table->string('foto2')->nullable();
            $table->string('foto3')->nullable();

            $table->integer('stok')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};