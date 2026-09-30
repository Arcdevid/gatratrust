<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The preceding migration owns spks. Extend it without replacing old records.
        if (! Schema::hasColumn('spks', 'project_id')) {
            Schema::table('spks', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('spks', 'data_proyek')) {
            Schema::table('spks', function (Blueprint $table) {
                $table->json('data_proyek')->nullable();
            });
        }

        if (! Schema::hasColumn('spks', 'pegawai_nama')) {
            return; // Installations already using the project-only schema need no conversion.
        }

        Schema::table('spks', function (Blueprint $table) {
            $table->string('pegawai_nama')->nullable()->change();
            $table->string('pegawai_jabatan')->nullable()->change();
            $table->string('tujuan_dinas')->nullable()->change();
            $table->date('tanggal_berangkat')->nullable()->change();
            $table->date('tanggal_kembali')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('spks', 'pegawai_nama')) {
            return;
        }

        // Refuse a rollback that would discard project links or invalidate new SPKs.
        $hasProjectData = DB::table('spks')->where(function ($query) {
            $query->whereNotNull('project_id')->orWhereNotNull('data_proyek');
            foreach (['pegawai_nama', 'pegawai_jabatan', 'tujuan_dinas', 'tanggal_berangkat', 'tanggal_kembali'] as $column) {
                $query->orWhereNull($column);
            }
        })->exists();

        if ($hasProjectData) {
            throw new RuntimeException('Rollback SPK dibatalkan: data SPK project harus dipertahankan.');
        }

        Schema::table('spks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn('data_proyek');
            $table->string('pegawai_nama')->nullable(false)->change();
            $table->string('pegawai_jabatan')->nullable(false)->change();
            $table->string('tujuan_dinas')->nullable(false)->change();
            $table->date('tanggal_berangkat')->nullable(false)->change();
            $table->date('tanggal_kembali')->nullable(false)->change();
        });
    }
};
