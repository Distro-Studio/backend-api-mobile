<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'data_karyawan_id' => 'integer',
        'jadwal_id' => 'integer',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'durasi' => 'integer',
        'lat' => 'string',
        'long' => 'string',
        'latkeluar' => 'string',
        'longkeluar' => 'string',
        'foto_masuk' => 'integer',
        'foto_keluar' => 'integer',
        'kategori_presensi_id' => 'integer',
        'note' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
