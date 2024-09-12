<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackRecord extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'kategori_record_id' => 'integer',
        'tgl_masuk' => 'date',
        'tgl_keluar' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
