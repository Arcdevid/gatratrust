<?php

namespace Database\Factories;

use App\Models\Kerjaan;
use App\Models\ProjectTbl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTbl>
 */
class ProjectTblFactory extends Factory
{
    protected $model = ProjectTbl::class;

    public function definition(): array
    {
        return [
            'nama_project' => fake()->sentence(3),
            'no_project' => 'PRJ-'.fake()->unique()->numerify('#####'),
            'client_id' => User::factory(),
            'kerjaan_id' => Kerjaan::factory(),
            'deskripsi' => fake()->sentence(),
            'start' => now()->toDateString(),
            'end' => now()->addDays(7)->toDateString(),
            'created_by' => User::factory(),
        ];
    }
}
