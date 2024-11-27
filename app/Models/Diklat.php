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
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'kuota' => 'integer',
        'durasi' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function image()
    {
        return $this->belongsTo(Berkas::class, 'gambar', 'id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriDiklat::class, 'kategori_diklat_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(StatusDiklat::class, 'status_diklat_id', 'id');
    }

    public function dokumen()
    {
        return $this->belongsTo(Berkas::class, 'dokumen_eksternal', 'id');
    }
}
