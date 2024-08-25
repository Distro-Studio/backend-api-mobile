<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiKantor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'alamat' => 'string',
        'lat' => 'string',
        'long' => 'string',
        'radius' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
