<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Lembur;
use App\Models\Notifikasi;
use App\Models\TukarJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CountController extends Controller
{
    public function countTukarandLembur()
    {
        try {
            $tukar = TukarJadwal::where('user_pengajuan', Auth::user()->id)->where('status_penukaran_id', 1)->count();
            $lembur = Lembur::where('user_id', Auth::user()->id)->where('status_lembur_id', 1)->count();
            $home = 0;
            $home = $tukar+$lembur;
            $notifikasi = Notifikasi::where('user_id', Auth::user()->id)->where('is_read', 0)->count();
            return response()->json(new DataResource(Response::HTTP_OK, 'Counting pending tukar jadwal berhasil', [
                'tukar_jadwal' => $tukar,
                'lembur' => $lembur,
                'home' => $home,
                'notifikasi' => $notifikasi,
            ]));
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
