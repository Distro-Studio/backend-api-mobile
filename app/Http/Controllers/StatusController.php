<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\PesertaDiklat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    public function getallstatuskaryawan()
    {
        try {
            $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->select('user_id', 'masa_diklat', 'status_reward_presensi')->first();

            $kategoriDiklat = [1, 2]; // 1 = Internal, 2 = Eksternal (ambil semua)
            // atau $kategoriDiklat = [1]; // Hanya internal
            // atau $kategoriDiklat = [2]; // Hanya eksternal

            $masadiklat = PesertaDiklat::where('peserta', $datakaryawan->user_id)
                ->whereHas('diklats', function ($query) use ($kategoriDiklat) {
                    $query->whereIn('kategori_diklat_id', $kategoriDiklat);
                })
                ->with('diklats')
                ->get()
                ->sum(function ($pesertaDiklat) {
                    return $pesertaDiklat->diklats->durasi;
                });

            $data = [
                'status_presensi' => $datakaryawan->status_reward_presensi,
                'masa_diklat' => $masadiklat
            ];

            return response()->json(new DataResource(Response::HTTP_OK, 'Status berhasil didapatkan', $data), Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $th->getMessage() . ' ' . $th->getLine()), Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
