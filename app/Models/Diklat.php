<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diklat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'gambar' => 'integer',
        'dokumen_eksternal' => 'integer',
        'kategori_diklat_id' => 'integer',
        'status_diklat_id' => 'integer',
        'kuota' => 'integer',
        'durasi' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
