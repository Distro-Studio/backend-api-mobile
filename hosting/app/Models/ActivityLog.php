<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class ActivityLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',                     // Untuk kolom 'id'
        'activity' => 'string',                // Untuk kolom 'activity'
        'kategori_activity_id' => 'integer',   // Untuk kolom 'kategori_activity_id'
        'user_id' => 'integer',                // Untuk kolom 'user_id'
        'created_at' => 'datetime',            // Untuk kolom 'created_at'
        'updated_at' => 'datetime',            // Untuk kolom 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->toDateString();
    }

    // Accessor untuk jam
    public function getJamAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->toTimeString();
    }

    public function kategoriActivy()
    {
        return $this->belongsTo(KategoriActivityLog::class, 'kategori_activity_id');
    }
}
