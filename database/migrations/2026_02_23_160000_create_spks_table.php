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
        Schema::create('spks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->date('tanggal');

            $table->string('pegawai_nama');
            $table->string('pegawai_jabatan');
            $table->string('pegawai_divisi')->nullable();
            $table->string('pegawai_nik_id')->nullable();

            $table->string('tujuan_dinas');
            $table->string('lokasi_perusahaan_tujuan')->nullable();
            $table->text('alamat_lokasi')->nullable();
            $table->text('maksud_ruang_lingkup')->nullable();

            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->unsignedSmallInteger('lama_perjalanan')->default(1);

            $table->string('sumber_biaya')->nullable();
            $table->enum('moda_transportasi', ['darat', 'laut', 'udara'])->default('darat');
            $table->enum('sumber_biaya_opsi', ['perusahaan', 'project', 'lainnya'])->default('perusahaan');

            $table->string('ditugaskan_oleh_nama')->default('Direktur Utama');
            $table->string('ditugaskan_oleh_jabatan')->default('Direktur');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spks');
    }
};
