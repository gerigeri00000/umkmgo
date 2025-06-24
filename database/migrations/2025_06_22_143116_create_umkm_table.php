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
        Schema::create('umkms', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('desa');
            $table->string('sls');
            $table->string('nama');
            $table->text('alamat');
            $table->string('website')->nullable();
            $table->string('telepon')->nullable();
            $table->string('jenis_tempat')->nullable();
            $table->string('jenis_usaha')->nullable();
            $table->string('kategori')->nullable();
            $table->string('koordinat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes untuk performance
            $table->index(['kategori', 'kecamatan', 'desa']);
            $table->index(['kecamatan', 'desa']);
            // $table->index(['provinsi', 'kabupaten']);
            // $table->index('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkms');
    }
};
