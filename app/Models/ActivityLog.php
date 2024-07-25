<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class ActivityLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

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
}
