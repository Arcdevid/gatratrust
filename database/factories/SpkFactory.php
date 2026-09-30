<?php

namespace Database\Factories;

use App\Models\ProjectTbl;
use App\Models\Spk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spk>
 */
class SpkFactory extends Factory
{
    protected $model = Spk::class;

    public function definition(): array
    {
        return [
            'nomor' => 'SPK-'.fake()->unique()->numerify('#####'),
            'tanggal' => now()->toDateString(),
            'project_id' => ProjectTbl::factory(),
            'data_proyek' => ['pembuatan_wps', 'running_pqr'],
        ];
    }
}
