<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Jadwal;
use App\Models\TukarJadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class JadwalController extends Controller
{
    public function gettodayjadwal()
    {
        try {
            $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
            if(!$jadwal){
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getalljadwal(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'tgl_mulai' => 'date',
        //     'tgl_selesai' => 'date'
        // ], [
        //     'tgl_mulai.date' => 'Tanggal mulai harus berupa tanggal',
        //     'tgl_selesai.date' => 'Tanggal selesai harus berupa tanggal',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        // }

        try {
            if ($request->tgl_mulai == null || $request->tgl_selesai == null) {
                // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo kosong'), Response::HTTP_OK);
                $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                $endOfWeek = now()->endOfWeek()->format('Y-m-d');

                $jadwal = Jadwal::where('user_id', Auth::user()->id)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->first();
                if(!$jadwal){
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

            } else if($request->tgl_mulai == '' || $request->tgl_selesai == '') {
                // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo kosong'), Response::HTTP_OK);
                $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                $endOfWeek = now()->endOfWeek()->format('Y-m-d');

                $jadwal = Jadwal::where('user_id', Auth::user()->id)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->first();
                if(!$jadwal){
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

            } else {
                // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo gak kosong'), Response::HTTP_OK);
                $jadwal = Jadwal::where('user_id', Auth::user()->id)->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])->with('shift')->first();
                if(!$jadwal){
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getanotherkaryawan(Jadwal $jadwal)
    {
        try {
            $jadwal = Jadwal::where('id', $jadwal->id)->with('shift')->first();
            if(!$jadwal){
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }
            $getuser = Jadwal::where('tgl_mulai', $jadwal->tgl_mulai)->where('shift_id', $jadwal->shift_id)->select('user_id')->with('user')->get();
            return response()->json(new DataResource(Response::HTTP_OK, 'Karyawan lain dengan jadwal yang sama berhasil di dapatkan', $getuser), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getuserjadwal(User $user)
    {
        try {
            $jadwal = Jadwal::where('user_id', $user->id)->where('tgl_mulai', '>', Carbon::today())->with('shift')->get();

            return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeschedule(Request $request)
    {
        try {
            $cek = Jadwal::where('id', $request->jadwal_id_ditukar)->first();
            if(!$cek)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            $jadwalawal = Jadwal::where('id', $request->jadwal_id_penukar)->first();

            if(!$jadwalawal)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            if($cek->user_id == $jadwalawal->user_id)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tidak bisa menukar jadwal anda sendiri'), Response::HTTP_NOT_ACCEPTABLE);
            }

            if($cek->tgl_mulai == $jadwalawal->tgl_mulai && $cek->shift_id == $jadwalawal->shift_id)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tidak bisa menukar jadwal yang sama'), Response::HTTP_NOT_ACCEPTABLE);
            }

            $tukarjadwal = TukarJadwal::create([
                'user_pengajuan' => $jadwalawal->user_id,
                'jadwal_pengajuan' => $jadwalawal->id,
                'user_ditukar' => $cek->user_id,
                'jadwal_ditukar' => $cek->id,
                'status_penukaran_id' => 1, //Menunggu
                'kategori_penukaran_id' => 1 //Tukar Shift
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil ditukar', $tukarjadwal), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
