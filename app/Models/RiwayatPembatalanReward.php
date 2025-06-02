<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPembatalanReward extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pembatalan_rewards';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'tipe_pembatalan' => 'string',
        'tgl_pembatalan' => 'date',
        'keterangan' => 'string',
        'cuti_id' => 'integer',
        'presensi_id' => 'integer',
        'riwayat_izin_id' => 'integer',
        'verifikator_1' => 'integer',
    ];

    public function dataKaryawan()
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id');
    }

    public function cuti()
    {
        return $this->belongsTo(Cuti::class, 'cuti_id', 'id');
    }

    public function riwayatizin()
    {
        return $this->belongsTo(RiwayatIzin::class, 'riwayat_izin_id', 'id');
    }
    
    public function verifikator1()
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }
}
