<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unitkerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function kompetensi()
    {
        return $this->belongsTo(Kompetensi::class, 'kompetensi_id');
    }

    public function kategoriagama()
    {
        return $this->belongsTo(KategoriAgama::class, 'kategori_agama_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'user_id', 'user_id');
    }

    public function statusKaryawan()
    {
        return $this->belongsTo(StatusKaryawan::class, 'status_karyawan_id', 'id');
    }
}
