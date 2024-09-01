<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'email' => 'string',
        'no_rm' => 'string',
        'no_manulife' => 'string',
        'tgl_masuk' => 'string',
        'tgl_keluar' => 'string',
        'unit_kerja_id' => 'integer',
        'jabatan_id' => 'integer',
        'kompetensi_id' => 'integer',
        'tunjangan_fungsional' => 'integer',
        'tunjangan_khusus' => 'integer',
        'tunjangan_lainnya' => 'integer',
        'uang_makan' => 'integer',
        'uang_lembur' => 'integer',
        'nik' => 'string',
        'nik_ktp' => 'string',
        'gelar_depan' => 'string',
        'tempat_lahir' => 'string',
        'tgl_lahir' => 'string',
        'alamat' => 'string',
        'no_hp' => 'string',
        'no_bpjsksh' => 'string',
        'tgl_diangkat' => 'string',
        'masa_kerja' => 'integer',
        'npwp' => 'string',
        'no_rekening' => 'string',
        'jenis_kelamin' => 'integer',
        'kategori_agama_id' => 'integer',
        'kategori_darah_id' => 'integer',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'pendidikan_terakhir' => 'integer',
        'no_ijazah' => 'string',
        'tahun_lulus' => 'integer',
        'no_kk' => 'string',
        'status_karyawan_id' => 'integer',
        'kelompok_gaji_id' => 'integer',
        'no_str' => 'string',
        'masa_berlaku_str' => 'string',
        'no_sip' => 'string',
        'masa_berlaku_sip' => 'string',
        'ptkp_id' => 'integer',
        'tgl_berakhir_pks' => 'string',
        'masa_diklat' => 'integer',
        'verifikator_1' => 'integer',
        'status_reward_presensi' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function unitkerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function kelompok_gaji(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_id', 'id');
    }

    public function ptkp(): BelongsTo
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function kompetensi()
    {
        return $this->belongsTo(Kompetensi::class, 'kompetensi_id');
    }

    public function kategoriagama()
    {
        return $this->belongsTo(KategoriAgama::class, 'kategori_agama_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'user_id', 'user_id');
    }

    public function statusKaryawan()
    {
        return $this->belongsTo(StatusKaryawan::class, 'status_karyawan_id', 'id');
    }

    public function golonganDarah()
    {
        return $this->belongsTo(KategoriDarah::class, 'kategori_darah_id', 'id');
    }

    public function pendidikanTerakhir()
    {
        return $this->belongsTo(KategoriPendidikan::class, 'pendidikan_terakhir', 'id');
    }

    public function riwayatperubahan()
    {
        return $this->hasMany(DataKaryawan::class, 'data_karyawan_id', 'id');
    }
}
