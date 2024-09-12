<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  protected $casts = [
    'id' => 'integer',
    'user_id' => 'integer',
    'jadwal_id' => 'integer',
    'tgl_pengajuan' => 'string',
    'durasi' => 'string',
    'catatan' => 'string',
    'status_lembur_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function jadwal()
  {
    return $this->belongsTo(Jadwal::class);
  }
}
