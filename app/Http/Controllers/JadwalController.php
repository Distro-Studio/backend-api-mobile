<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\Jadwal;
use App\Models\NonShift;
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
            $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
            if($datakaryawan->unitkerja->jenis_karyawan == 1) {
                $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
            }else {
                $nonshift = NonShift::where('id', 1)->first();
                $jadwaln = [
                    "id" => 0,
                    "user_id" => $datakaryawan->user_id,
                    "tgl_mulai" => date('Y-m-d'),
                    "tgl_selesai" => date('Y-m-d'),
                    "shift_id" => 0,
                    "created_at" => null,
                    "updated_at" => null,
                    "shift" => [
                        "id" => 0,
                        "nama" => "Siang",
                        "jam_from" => $nonshift->jam_from,
                        "jam_to" => $nonshift->jam_to,
                        "deleted_at" => null,
                        "created_at" => null,
                        "updated_at" => null
                    ]
                ];

                $encode = json_encode($jadwaln);

                $jadwal = json_decode($encode);
            }

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

                $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->get();
                if($jadwal->isEmpty()){
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

            } else if($request->tgl_mulai == '' || $request->tgl_selesai == '') {
                // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo kosong'), Response::HTTP_OK);
                $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                $endOfWeek = now()->endOfWeek()->format('Y-m-d');

                $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->get();
                if($jadwal->isEmpty()){
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

            } else {
                // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo gak kosong'), Response::HTTP_OK);
                $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])->with('shift')->get();
                if($jadwal->isEmpty()){
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

            $kategori = 1; // default Tukar Shift

            if ($cek->shift_id == null){
                $kategori = 2; // diubah menjadi Tukar Libur
            }

            $tukarjadwal = TukarJadwal::create([
                'user_pengajuan' => $jadwalawal->user_id,
                'jadwal_pengajuan' => $jadwalawal->id,
                'user_ditukar' => $cek->user_id,
                'jadwal_ditukar' => $cek->id,
                'status_penukaran_id' => 1, //Menunggu
                'kategori_penukaran_id' => $kategori
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil ditukar', $tukarjadwal), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getjadwalditukar(Jadwal $jadwal)
    {
        try {
            if ($jadwal->shift_id == null)
            {
                $karyawanA = Jadwal::where('user_id', Auth::user()->id)
                    ->where('shift_id', null)
                    ->where('tgl_mulai', '>=', date('Y-m-d'))
                    ->get();

                $validSchedules = [];

                foreach($karyawanA as $schedule) {
                    $karyawanB = Jadwal::where('tgl_mulai', $schedule->tgl_mulai)
                        ->where('shift_id', null)
                        ->where('user_id', $jadwal->user_id)
                        ->exists();

                    if (!$karyawanB) {
                        // Jika karyawan B tidak libur pada hari yang sama
                        $validSchedules[] = $schedule;
                    }
                }

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal ditukar', $validSchedules), Response::HTTP_OK);
            } else {
                $schedule = Jadwal::where('user_id', Auth::user()->id)
                    ->where('shift_id', '!=', null)
                    ->where('tgl_mulai', '>=', date('Y-m-d'))
                    ->with('shift')
                    ->get();

                return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal ditukar', $schedule), Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
