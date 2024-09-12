<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitKerja>
 */
class UnitKerjaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_unit' => $this->faker->randomElement(['Akutansi', 'HRD', 'Apotek', 'Perawat']),
            'jenis_karyawan' => $this->faker->randomElement([1,0])
        ];
    }
}
