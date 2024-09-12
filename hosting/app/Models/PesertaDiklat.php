<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaDiklat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'diklat_id' => 'integer',
        'peserta' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function diklat()
    {
        return $this->belongsTo(Diklat::class, 'diklat_id', 'id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'peserta', 'id');
    }
}
