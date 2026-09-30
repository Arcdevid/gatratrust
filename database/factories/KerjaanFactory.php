<?php

namespace Database\Factories;

use App\Models\Kerjaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kerjaan>
 */
class KerjaanFactory extends Factory
{
    protected $model = Kerjaan::class;

    public function definition(): array
    {
        return [
            'nama_kerjaan' => fake()->unique()->jobTitle(),
        ];
    }
}
