<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPenggajian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'tgl_mulai' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
