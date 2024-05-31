<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataKaryawan>
 */
class DataKaryawanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 8,
            // 'no_rm' => $this->faker->randomNumber(),
            // 'no_manulife' => $this->faker->randomNumber(),
            // 'tgl_masuk' => $this->faker->date(),
            // 'unit_kerja_id' => $this->faker->randomElement([1,3,5]),
            // 'jabatan_id' => $this->faker->randomElement([1,2,3,4,5]),
            // 'kompetensi_id' => $this->faker->randomElement([6,7,8,9,10])
        ];
    }
}
