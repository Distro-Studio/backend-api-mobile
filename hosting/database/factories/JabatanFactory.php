<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jabatan>
 */
class JabatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jabatanRumahSakit = [
            "Direktur atau Kepala Rumah Sakit",
            "Wakil Direktur Bagian Pelayanan",
            "Wakil Direktur Bagian Administrasi dan Keuangan",
            "Wakil Direktur Bagian Penunjang",
            "Divisi Administrasi Keuangan dan Akuntansi",
            "Divisi Sumber Daya Manusia",
            "Divisi Public Relation",
            "Divisi Pelayanan Medik",
            "Divisi Keperawatan",
            "Divisi Farmasi",
            "Divisi IGD (Instalasi Gawat Darurat)",
            "Divisi Penunjang Medik",
            "Divisi Penunjang Non Medik"
        ];

        return [
            'nama_jabatan' => $this->faker->randomElement($jabatanRumahSakit),
            'is_struktural' => $this->faker->randomElement([true,false]),
            'tunjangan' => '1000000'
        ];
    }
}
