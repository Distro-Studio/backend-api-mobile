<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukarJadwal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_pengajuan' => 'integer',
        'jadwal_pengajuan' => 'integer',
        'user_ditukar' => 'integer',
        'jadwal_ditukar' => 'integer',
        'status_penukaran_id' => 'integer',
        'kategori_penukaran_id' => 'integer',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function userPengajuan()
    {
        return $this->belongsTo(User::class, 'user_pengajuan', 'id');
    }

    public function userDitukar()
    {
        return $this->belongsTo(User::class, 'user_ditukar', 'id');
    }

    public function jadwalTukar()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_pengajuan', 'id');
    }

    public function jadwalDitukar()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_ditukar', 'id');
    }

    public function statusPengajuan()
    {
        return $this->belongsTo(StatusTukarJadwal::class, 'status_penukaran_id', 'id');
    }

    public function kategoriPengajuan()
    {
        return $this->belongsTo(KategoriTukarJadwal::class, 'kategori_penukaran_id', 'id');
    }
}
