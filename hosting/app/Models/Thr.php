<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thr extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'perhitungan' => 'string',
        'nominal_satu' => 'integer',
        'nominal_dua' => 'integer',
        'potongan' => 'string',
        'kriteria_karyawan_kontrak' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
