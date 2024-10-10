<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriPelatihan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class, 'pj_materi', 'id');
    }

    public function berkas_1()
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_1', 'id');
    }

    public function berkas_2()
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_2', 'id');
    }

    public function berkas_3()
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_3', 'id');
    }


}
