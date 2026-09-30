<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor',
        'tanggal',
        'project_id',
        'data_proyek',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'data_proyek' => 'array',
    ];

    public const DATA_PROYEK_OPTIONS = [
        'pembuatan_wps' => 'Pembuatan WPS',
        'running_pqr' => 'Running PQR',
        'running_wqt' => 'Running WQT',
        'pengujian_material' => 'Pengujian Material',
        'koordinasi_ndt' => 'Koordinasi NDT',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectTbl::class, 'project_id');
    }
}
