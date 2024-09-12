<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kompetensi>
 */
class KompetensiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $profesiRumahSakit = array(
            "Dokter",
            "Apoteker dan Tenaga Teknis Kefarmasian",
            "Ahli Gizi",
            "Perawat",
            "Radiografer",
            "Ahli Teknologi Laboratorium Medis dan Klinis",
            "Ahli Optometri",
            "Teknisi Rekam Medis",
            "Kasir Rumah Sakit"
        );

        return [
            'nama_kompetensi' => $this->faker->randomElement($profesiRumahSakit),
            'total_tunjangan' => $this->faker->randomNumber(7),
            'jenis_kompetensi' => $this->faker->randomElement([1,0]),
        ];
    }
}
