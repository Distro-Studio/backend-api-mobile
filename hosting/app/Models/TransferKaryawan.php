<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'tgl_mulai' => 'string',
        'unit_kerja_asal' => 'integer',
        'unit_kerja_tujuan' => 'integer',
        'jabatan_asal' => 'integer',
        'jabatan_tujuan' => 'integer',
        'kelompok_gaji_asal' => 'integer',
        'kelompok_gaji_tujuan' => 'integer',
        'role_asal' => 'integer',
        'role_tujuan' => 'integer',
        'kategori_transfer_id' => 'integer',
        'alasan' => 'string',
        'dokumen' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
