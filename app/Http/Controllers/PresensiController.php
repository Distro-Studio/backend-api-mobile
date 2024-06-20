<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PresensiController extends Controller
{
    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'lat' => 'required',
        //     'long' => 'required'
        // ], [
        //     'lat.required' => 'Latitude harus diisi',
        //     'long.required' => 'Longitude harus diisi'
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        // }

        $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
        $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tanggal', date('Y-m-d'))->with('shift')->first();

        $start = Carbon::createFromTimeString($jadwal->shift->jam_from, 'Asia/Jakarta');
        $end = Carbon::createFromTimeString(date('H:i:s'), 'Asia/Jakarta');
        // $start->locale('id');
        // $end->locale('id');

        $diff = $start->diffForHumans($end);

        if ($end->equalTo($start)) {
            $status = 'tepat waktu';
        } elseif ($end->lessThan($start)) {
            $status = 'lebih awal';
        } else {
            $status = 'terlambat';
        }


        return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', [
            'start' => $start,
            'end' => $end,
            'diff' => $diff,
            'message' => $status
        ]), Response::HTTP_OK);
    }
}
