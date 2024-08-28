<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerubahanKeluarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_perubahan_id' => 'integer',
        'data_keluarga_id' => 'integer',
        'nama_keluarga' => 'string',
        'hubungan' => 'string', // Karena ENUM di-cast sebagai string
        'pendidikan_terakhir' => 'string',
        'status_hidup' => 'boolean', // 'tinyint(1)' biasanya digunakan untuk menyimpan nilai boolean
        'pekerjaan' => 'string',
        'no_hp' => 'string',
        'email' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
