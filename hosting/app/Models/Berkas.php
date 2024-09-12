<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'file_id' => 'string',
        'nama' => 'string',
        'kategori_berkas_id' => 'integer',
        'status_berkas_id' => 'integer',
        'path' => 'string',
        'tgl_upload' => 'datetime',
        'nama_file' => 'string',
        'ext' => 'string',
        'size' => 'string',
        'verifikator_1' => 'integer',
        'alasan' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
