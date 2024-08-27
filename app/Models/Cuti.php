<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'tipe_cuti_id' => 'integer',
        'tgl_from' => 'string',
        'tgl_to' => 'string',
        'catatan' => 'string',
        'durasi' => 'integer',
        'status_cuti_id' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
        'alasan' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tipeCuti()
    {
        return $this->belongsTo(TipeCuti::class, 'tipe_cuti_id', 'id');
    }

    public function statuscuti()
    {
        return $this->belongsTo(StatusCuti::class, 'status_cuti_id', 'id');
    }
}
