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
    'jenis_kelamin' => 'integer',
    'status_hidup' => 'boolean',
    'pekerjaan' => 'string',
    'status_keluarga_id' => 'integer',
    'is_menikah' => 'integer',
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

  public function statusKeluarga()
  {
    return $this->belongsTo(StatusKeluarga::class, 'status_keluarga_id', 'id');
  }

  public function kategoriAgama()
  {
    return $this->belongsTo(KategoriAgama::class, 'kategori_agama_id', 'id');
  }

  public function kategoriDarah()
  {
    return $this->belongsTo(KategoriDarah::class, 'kategori_darah_id', 'id');
  }

}
