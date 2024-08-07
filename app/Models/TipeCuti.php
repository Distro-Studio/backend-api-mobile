<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TipeCuti extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Akses nilai 'used' dengan default range (tanpa filter)
    // public function getUsedAttribute()
    // {
    //     // Misalnya tanpa filter tanggal, kembalikan semua
    //     return $this->calculateUsed();
    // }

    // // Metode untuk menghitung 'used' dengan rentang tanggal
    // public function calculateUsed($startDate = null, $endDate = null)
    // {
    //     $query = \App\Models\Cuti::where('tipe_cuti_id', $this->id)->where('user_id', Auth::user()->id);

    //     if ($startDate && $endDate) {
    //         $query->whereBetween('created_at', [$startDate, $endDate]);
    //     }

    //     // $query->get();
    //     // dd($user_id);
    //     // dd($query->toSql(), $query->getBindings());

    //     return $query->count();
    // }

}
