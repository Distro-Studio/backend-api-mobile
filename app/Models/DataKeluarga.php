<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKeluarga extends Model
{
  use HasFactory;

  protected $guarded = ['id'];

  protected $casts = [
    'id' => 'integer',
    'data_karyawan_id' => 'integer',
    'nama_keluarga' => 'string',
    'hubungan' => 'string',
    'pendidikan_terakhir' => 'string',
    'status_hidup' => 'boolean',
    'pekerjaan' => 'string',
    'no_hp' => 'string',
    'email' => 'string',
    'is_bpjs' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  public function pendidikanTerakhir()
  {
    return $this->belongsTo(KategoriPendidikan::class, 'pendidikan_terakhir', 'id');
  }

}
