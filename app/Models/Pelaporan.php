<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelaporan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor', 'id');
    }

    public function pelaku(){
        return $this->belongsTo(User::class, 'pelaku', 'id');
    }
}
