<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatIzin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'tgl_izin' => 'string',
        'waktu_izin' => 'string',
        'durasi' => 'integer',
        'keterangan' => 'string',
        'status_izin_id' => 'integer',
        'verifikator_1' => 'integer',
        'alasan' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function statusizin()
    {
        return $this->belongsTo(StatusRiwayatIzin::class, 'status_izin_id', 'id');
    }

}
