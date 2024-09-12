<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Premi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'nama_premi' => 'string',
        'kategori_potongan_id' => 'integer',
        'jenis_premi' => 'boolean',
        'besaran_premi' => 'integer',
        'minimal_rate' => 'integer',
        'maksimal_rate' => 'integer',
        'has_custom_formula' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
