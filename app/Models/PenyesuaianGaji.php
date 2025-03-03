<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenyesuaianGaji extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'penggajian_id' => 'integer',
        'kategori_gaji_id' => 'integer',
        'nama_detail' => 'string',
        'besaran' => 'integer',
        'bulan_mulai' => 'string',
        'bulan_selesai' => 'string',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the penggajians that owns the PenyesuaianGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penggajians(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class, 'penggajian_id', 'id');
    }

    /**
     * Get the kategori_gajis  that owns the PenyesuaianGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_gajis(): BelongsTo
    {
        return $this->belongsTo(KategoriGaji::class, 'kategori_gaji_id', 'id');
    }
}
