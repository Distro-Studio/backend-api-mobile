<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HakCuti extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'kuota' => 'integer',
        'used_kuota' => 'integer',
    ];

    public function datakaryawan()
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    public function tipecuti()
    {
        return $this->belongsTo(TipeCuti::class, 'tipe_cuti_id', 'id');
    }
}
