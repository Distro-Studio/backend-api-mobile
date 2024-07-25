<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function datakaryawan()
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function fotomasuk()
    {
        return $this->belongsTo(Berkas::class, 'foto_masuk');
    }

    public function fotokeluar()
    {
        return $this->belongsTo(Berkas::class, 'foto_keluar');
    }
}
