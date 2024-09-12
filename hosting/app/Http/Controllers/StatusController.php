<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    public function getallstatuskaryawan()
    {
        try {
            $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->select('user_id', 'masa_diklat', 'status_reward_presensi')->first();
            $data = [
                'status_presensi' => $datakaryawan->status_reward_presensi,
                'masa_diklat' => $datakaryawan->masa_diklat
            ];

            return response()->json(new DataResource(Response::HTTP_OK, 'Status berhasil didapatkan', $data), Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Something wrong'), Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
