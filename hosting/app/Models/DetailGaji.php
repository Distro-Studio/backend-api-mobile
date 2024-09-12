<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailGaji extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'penggajian_id' => 'integer',
        'kategori_gaji_id' => 'integer',
        'nama_detail' => 'string',
        'besaran' => 'integer',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function penggajians(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class, 'penggajian_id', 'id');
    }

    /**
     * Get the kategori_gajis that owns the DetailGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_gajis(): BelongsTo
    {
        return $this->belongsTo(KategoriGaji::class, 'kategori_gaji_id', 'id');
    }
}
