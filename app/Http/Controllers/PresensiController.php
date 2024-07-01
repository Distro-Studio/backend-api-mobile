<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\Jadwal;
use App\Models\Presensi;
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
        $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();

        // $start = Carbon::createFromTimeString($jadwal->shift->jam_from, 'Asia/Jakarta');
        $start = Carbon::createFromFormat('H:i:s', $jadwal->shift->jam_from, 'Asia/Jakarta');
        // $end = Carbon::createFromTimeString(date('H:i:s'), 'Asia/Jakarta');
        $end = Carbon::now();
        // $start->locale('id');
        // $end->locale('id');

        // $diff = $start->diffForHumans($end);

        // if ($end->equalTo($start)) {
        //     $status = 'tepat waktu';
        // } elseif ($end->lessThan($start)) {
        //     $status = 'terlambat';
        // } else {
        //     $status = 'lebih awal';
        // }

        if ($end->gt($start)) {
            $differenceInMinutes = $end->diffInMinutes($start);
            $status = "Karyawan terlambat $differenceInMinutes menit.";
        } elseif ($end->eq($start)) {
            $status = "Karyawan tepat waktu.";
        } else {
            $differenceInMinutes = $start->diffInMinutes($end);
            $status = "Karyawan datang lebih awal $differenceInMinutes menit.";
        }

        // $presensi = Presensi::create([]);


        return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', [
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            // 'diff' => $diff,
            'message' => $status
        ]), Response::HTTP_OK);
    }
}
