<?php

namespace Database\Seeders;

use App\Models\Kompetensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KompetensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kompetensi::factory(5)->create();
    }
}
