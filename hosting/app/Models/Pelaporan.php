<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelaporan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'pelapor' => 'integer',
        'pelaku' => 'integer',
        'tgl_kejadian' => 'datetime',
        'lokasi' => 'string',
        'kronologi' => 'string',
        'upload_foto' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor', 'id');
    }

    public function pelaku(){
        return $this->belongsTo(User::class, 'pelaku', 'id');
    }
}
