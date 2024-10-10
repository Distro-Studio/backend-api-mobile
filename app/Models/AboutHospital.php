<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutHospital extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function berkas_1()
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_1', 'id');
    }

    public function berkas_2()
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_2', 'id');
    }

    public function berkas_3()
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_3', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'edited_by', 'id');
    }
}
