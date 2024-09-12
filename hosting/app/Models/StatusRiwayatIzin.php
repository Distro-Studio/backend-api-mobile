<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusRiwayatIzin extends Model
{
    use HasFactory;
    // protected $table = 'status_riwayat_izin';
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'label' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
