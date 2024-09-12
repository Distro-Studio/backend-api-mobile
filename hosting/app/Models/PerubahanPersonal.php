<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerubahanPersonal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_perubahan_id' => 'integer',
        'tempat_lahir' => 'string',
        'tgl_lahir' => 'date', // Anda mungkin ingin mengubah ini menjadi 'date' jika format tanggal disimpan sebagai DATE
        'no_hp' => 'string',
        'jenis_kelamin' => 'integer', // 'tinyint(1)' biasanya digunakan untuk menyimpan nilai boolean
        'nik_ktp' => 'string',
        'no_kk' => 'string',
        'kategori_agama_id' => 'integer',
        'kategori_darah_id' => 'integer',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'alamat' => 'string',
        'no_ijasah' => 'string',
        'tahun_lulus' => 'integer',
        'pendidikan_terakhir' => 'integer',
        'gelar_depan' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
