<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriDiklat extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the diklats for the KategoriDiklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function diklats(): HasMany
    {
        return $this->hasMany(Diklat::class, 'kategori_diklat_id', 'id');
    }
}
