<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPerubahan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'jenis_perubahan' => 'string',
        'kolom' => 'string',
        'status_perubahan_id' => 'integer',
        'verifikator_1' => 'integer',
        'alasan' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
