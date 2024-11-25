<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Cuti;
use App\Models\DataKaryawan;
use App\Models\HariLibur;
use App\Models\Jadwal;
use App\Models\LokasiKantor;
use App\Models\NonShift;
use App\Models\Notifikasi;
use App\Models\Presensi;
use App\Models\Shift;
use App\Models\TukarJadwal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Carbon\CarbonPeriod;
// use Illuminate\Database\Eloquent\ModelNotFoundException;

class JadwalController extends Controller
{
  public function gettodayjadwal()
  {
    try {
      $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
      $officeloc = LokasiKantor::where('id', 1)->first();
      $aktivitas = false;



      if ($datakaryawan->unitkerja->jenis_karyawan == 1) {
        //cek jadwal kemarin jika shift malam
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $now = Carbon::now();
        // $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
        $jadwal = Jadwal::where('user_id', Auth::user()->id)
        ->where(function ($query) use ($today, $yesterday, $now) {
            // Kondisi 1: Jadwal hari ini
            $query->whereDate('tgl_mulai', $today);
              // ->whereHas('shift', function ($shiftQuery) use ($now) {
              //   $shiftQuery->where('jam_to', '>=', $now->format('H:i:s'));
              // });

            // Kondisi 2: Shift malam
            $query->orWhere(function ($query) use ($today, $yesterday, $now) {
                $query->whereDate('tgl_mulai', $yesterday)
                    ->whereDate('tgl_selesai', '>=', $today)
                    ->whereHas('shift', function ($shiftQuery) use ($now) {
                        $shiftQuery->where('jam_to', '>=', $now->format('H:i:s'));
                    });
            });
        })->with('shift')
        ->first();

        // $startTime = Carbon::parse(->jam_masuk)
        // $endTime = Carbon::parse($time);
        // $duration = $startTime->diff($endTime);
        // return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, $jadwal->id), Response::HTTP_NOT_FOUND);
        if ($jadwal) {
            $cekpresensi = Presensi::where('user_id', Auth::user()->id)->where('jadwal_id', $jadwal->id)->with(['jadwal.shift'])->first();


            if ($cekpresensi) {
                if ($cekpresensi->jam_keluar == null) {
                    $aktivitas = true;

                } else {
                    $aktivitas = false;
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Presensi sudah dilakukan'), Response::HTTP_NOT_FOUND);
                }
            }
          $jadwal->office_lat = $officeloc->lat;
          $jadwal->office_long = $officeloc->long;
          $jadwal->radius = $officeloc->radius;
          $jadwal->aktivitas = $aktivitas;
        }
      } else {
        $hari = [
          'Sunday' => 'Minggu',
          'Monday' => 'Senin',
          'Tuesday' => 'Selasa',
          'Wednesday' => 'Rabu',
          'Thursday' => 'Kamis',
          'Friday' => 'Jumat',
          'Saturday' => 'Sabtu',
        ];
        $waktuSekarang = Carbon::now();
        $nonshift = NonShift::where('nama', $hari[$waktuSekarang->isoFormat('dddd')])->first();

        if(!$nonshift){
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }

        $jamMasuk = Carbon::parse($nonshift->jam_from);
        $jamKeluar = Carbon::parse($nonshift->jam_to);

        // return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, $hari[$waktuSekarang->isoFormat('dddd')]), Response::HTTP_NOT_FOUND);
        // if (!$waktuSekarang->between($jamMasuk, $jamKeluar)) {
        //   return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        // }

        if (Carbon::now()->isSunday()) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }

        $cekpresensi = Presensi::where('user_id', Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->first();
        if ($cekpresensi) {
          if ($cekpresensi->jam_keluar == null) {
            $aktivitas = true;
          } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Presensi sudah dilakukan'), Response::HTTP_NOT_FOUND);
          }
        }

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
          ],
          "office_lat" => $officeloc->lat,
          "office_long" => $officeloc->long ?? null,
          "radius" => $officeloc->radius ?? null,
          "aktivitas" => $aktivitas,
        ];

        $encode = json_encode($jadwaln);

        $jadwal = json_decode($encode);




      }

      if (!$jadwal) {
        $cekpresensik = Presensi::where('user_id', Auth::user()->id)->whereMonth('created_at', Carbon::now()->month)->whereDate('created_at', '<', Carbon::now()->format('Y-m-d H:i:s'))->get();
        // $reward = true;
        foreach($cekpresensik as $c) {
          if ($c->jam_keluar == null) {
            // $reward = false;
            $datakaryawan->status_reward_presensi = 0;
            $datakaryawan->save();
            break;
          }
        }
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      if($jadwal->shift_id == 0){
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      if(!$aktivitas) {
        $time = date('Y-m-d H:i:s');
        $schDate = Carbon::parse($jadwal->shift->jam_from);
        $endDate = Carbon::parse($jadwal->shift->jam_to);
        $nowTime = Carbon::parse($time);
        $duration = $schDate->diffInSeconds($nowTime);
        $jadwal->duration = $duration;

        if($nowTime->lessThan($schDate)){
          if ($duration > 7200 ) {
              return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Absensi belum dimulai', $jadwal), Response::HTTP_NOT_FOUND);
          }

        }

        if($nowTime->greaterThan($endDate)){
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
      }


      // $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $jadwal->tgl_selesai . ' ' . $jadwal->shift->jam_to);
      // if(Carbon::now()->greaterThan($endTime)) {
      //   $datakaryawan->status_reward_presensi = 0;
      //   $datakaryawan->save();
      // }
      return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getLine()), Response::HTTP_INTERNAL_SERVER_ERROR);
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
      $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
      $hariLibur = HariLibur::all()->pluck('tanggal')->toArray();
      $nonShift = NonShift::where('id', 1)->first();
      // $start = Carbon::now()->startOfWeek();
      // $end = Carbon::now()->endOfWeek();
      // $startDate = Carbon::createFromFormat('Y-m-d', $start);
      // $endDate = Carbon::createFromFormat('Y-m-d', $end);
      // $startDate = Carbon::now('Asia/Jakarta')->startOfWeek();
      // $endDate = Carbon::now('Asia/Jakarta')->endOfWeek();
      $startDate = Carbon::createFromFormat('Y-m-d', $request->tgl_mulai);
      $endDate = Carbon::createFromFormat('Y-m-d', $request->tgl_selesai);

      // if($request->filled('tgl_mulai')) {
      //     $startDate = Carbon::createFromFormat('Y-m-d', $request->tgl_mulai, 'Asia/Jakarta');
      // }

      // if($request->filled('tgl_selesai')) {
      //     $endDate = Carbon::createFromFormat('Y-m-d', $request->tgl_selesai);
      // }

      // return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Jadwal berhasil didapatkan', $startDate), Response::HTTP_NOT_FOUND);
      $cuti = Cuti::where('user_id', Auth::user()->id)->where('status_cuti_id', 4)->get();

      $listtglcuti = [];

      foreach($cuti as $c) {
        $tglfrom = Carbon::parse($c->tgl_from)->addDay();
        $tglto = Carbon::parse($c->tgl_to)->addDay();
        $period = CarbonPeriod::create($tglfrom->format('Y-m-d'), $tglto->format('Y-m-d'));

        foreach ($period as $dt) {
            $listtglcuti[] = $dt->format('Y-m-d');
        }
      }


      if ($datakaryawan->unitkerja->jenis_karyawan == 0) {
        $date_range = $this->generateDateRange($startDate, $endDate);
        foreach ($date_range as $date) {
          $day_of_week = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;
          $harilibur = HariLibur::whereDate('tanggal', $date)->first();
          if ($day_of_week == Carbon::SUNDAY) {
            // Libur pada hari Minggu
            // $user_schedule_array[$date] = [
            //     "id" => 0,
            //     "user_id" => Auth::user()->id,
            //     "tgl_mulai" => $day_of_week,
            //     "tgl_selesai" => $day_of_week,
            //     "shift_id" => 0,
            //     "created_at" => $day_of_week,
            //     "updated_at" => $day_of_week,
            //     "shift" => [
            //         "id" => 0,
            //         "nama" => "Libur Minggu",
            //         "jam_from" => "06:00:00",
            //         "jam_to" => "16:00:00",
            //         "deleted_at" => null,
            //         "created_at" => "2024-08-28T07:12:54.000000Z",
            //         "updated_at" => "2024-08-28T07:12:54.000000Z"
            //     ]
            // ];
          } else if ($harilibur) {
            // $user_schedule_array[$date] = [
            //   'id' => $hariLibur[$date]->id,
            //   'nama' => $hariLibur[$date]->nama,
            //   'jam_from' => null,
            //   'jam_to' => null,
            //   'status' => 3 // libur besar
            // ];
          } else if (in_array($date, $listtglcuti)) {

          } else if ($nonShift) {
              $user_schedule_array[$date] = [
                "id" => 0,
                "user_id" => Auth::user()->id,
                "tgl_mulai" => $date,
                "tgl_selesai" => $date,
                "shift_id" => 0,
                "created_at" => $date,
                "updated_at" => $date,
                "shift" => [
                  "id" => 0,
                  "nama" => "Jam Kerja",
                  "jam_from" => $nonShift->jam_from,
                  "jam_to" => $nonShift->jam_to,
                  "deleted_at" => null,
                  "created_at" => null,
                  "updated_at" => null
                ]
              ];
          }
        }

        $result = array_values($user_schedule_array);
      } else {
        $result = Jadwal::where('user_id', Auth::user()->id)->whereBetween('tgl_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->with('shift')->orderBy('tgl_mulai', 'asc')->get();
        // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $startDate->format('Y-m-d') . ' ' . $endDate->format('Y-m-d')), Response::HTTP_OK);
        if ($result->isEmpty()) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
      }

      return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $result), Response::HTTP_OK);
    //   return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $listtglcuti), Response::HTTP_OK);

      // if ($request->tgl_mulai == null || $request->tgl_selesai == null) {
      //     // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo kosong'), Response::HTTP_OK);

      //     $start = Carbon::now()->startOfWeek();
      //     $end = Carbon::now()->endOfWeek();
      //     $startOfWeek = Carbon::createFromFormat('Y-m-d', $start);
      //     $endOfWeek = Carbon::createFromFormat('Y-m-d', $end);


      //     if($datakaryawan->unitkerja->jenis_karyawan == 0) {
      //         $jadwal = [];
      //         for ($date = $start; $date <= $end; $date->addDay()) {
      //             // Format tanggal ke Y-m-d
      //             $formattedDate = $date->format('Y-m-d');

      //             // Cek apakah tanggal ini adalah hari libur
      //             if (in_array($formattedDate, $harilibur)) {
      //                 $jadwal = [
      //                     'id' => null,
      //                     "tgl" => $formattedDate,
      //                     "jam_from" => null,
      //                     "jam_to" => null,
      //                 ];
      //             } else {
      //                 $jadwal = [
      //                     'id' => 1,
      //                     "tgl" => $formattedDate,
      //                     "jam_from" => $nonshift->jam_from,
      //                     "jam_to" => $nonshift->jam_to,
      //                 ];
      //             }
      //         }

      //         // return response()->json(new WithoutDataResource(Response::HTTP_OK, $startDate->diffInDays($endDate) + 1), Response::HTTP_OK);
      //     }else {
      //         $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->get();
      //         if($jadwal->isEmpty()){
      //             return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      //         }
      //     }


      //     return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

      // } else if($request->tgl_mulai == '' || $request->tgl_selesai == '') {
      //     // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo kosong'), Response::HTTP_OK);
      //     $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
      //     $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
      //     if($datakaryawan->unitkerja->jenis_karyawan == 0) {
      //         // $jadwal = [
      //         //     'id' => 0,
      //         //     "user_id" => 8,
      //         //     "tgl_mulai" => "2024-07-12",
      //         //     "tgl_selesai" => "2024-07-12",
      //         //     "shift_id" => 3,
      //         //     "created_at" => null,
      //         //     "updated_at" => null,
      //         // ];

      //         return response()->json(new WithoutDataResource(Response::HTTP_OK, $startOfWeek), Response::HTTP_OK);
      //     }
      //     $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$startOfWeek, $endOfWeek])->with('shift')->get();
      //     if($jadwal->isEmpty()){
      //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      //     }

      //     return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);

      // } else {
      //     // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', 'ini kalo gak kosong'), Response::HTTP_OK);
      //     // $start = Carbon::now()->startOfWeek();
      //     // $end = Carbon::now()->endOfWeek();
      //     $startOfWeek = Carbon::createFromFormat('Y-m-d', $request->tgl_mulai);
      //     $endOfWeek = Carbon::createFromFormat('Y-m-d', $request->tgl_selesai);


      //     if($datakaryawan->unitkerja->jenis_karyawan == 0) {
      //         $jadwal = [];
      //         for ($date = $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
      //             // Format tanggal ke Y-m-d
      //             $formattedDate = $date->format('Y-m-d');

      //             // Cek apakah tanggal ini adalah hari libur
      //             if (in_array($formattedDate, $harilibur)) {
      //                 $jadwal = [
      //                     'id' => null,
      //                     "tgl" => $formattedDate,
      //                     "jam_from" => null,
      //                     "jam_to" => null,
      //                 ];
      //             } else {
      //                 $jadwal = [
      //                     'id' => 1,
      //                     "tgl" => $formattedDate,
      //                     "jam_from" => $nonshift->jam_from,
      //                     "jam_to" => $nonshift->jam_to,
      //                 ];
      //             }
      //         }

      //         // return response()->json(new WithoutDataResource(Response::HTTP_OK, $startDate->diffInDays($endDate) + 1), Response::HTTP_OK);
      //     }else {
      //         $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('shift_id', '!=', null)->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])->with('shift')->get();
      //         if($jadwal->isEmpty()){
      //             return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      //         }
      //     }

      //     return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
      // }


    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function getanotherkaryawan($jadwals)
  {

    try {
      if ($jadwals != 0) {
        // $jadwal = Jadwal::find($jadwals);
        $jadwal = Jadwal::where('id', $jadwals)->with('shift')->first();
        if (!$jadwal) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        // $getuser = Jadwal::where('tgl_mulai', $jadwal->tgl_mulai)->where('shift_id', $jadwal->shift_id)->where('user_id', '!=', $jadwal->user_id)->select('user_id')->with('user')->with(['user.dataKaryawan.kompetensi', 'user.dataKaryawan.statusKaryawan'])->get();
        $getuser = Jadwal::where('tgl_mulai', $jadwal->tgl_mulai)->where('user_id', '!=', $jadwal->user_id)->select('user_id')->with('user')->with(['user.dataKaryawan.kompetensi', 'user.dataKaryawan.statusKaryawan'])->get();
        $data = $getuser->map(function ($item) {
              return [
                'user_id' => $item->user_id,
                'user' => $item->user,
                'kompetensi' => $item->user->dataKaryawan->kompetensi,
                'status_karyawan' => $item->user->dataKaryawan->statusKaryawan, // Mengambil data kompetensi
              ];
        });
      } else {
        $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
        $getuser = DataKaryawan::where('unit_kerja_id', $datakaryawan->unit_kerja_id)->where('user_id', '!=', Auth::user()->id)->with('user', 'kompetensi', 'statusKaryawan')->get();
        $data = $getuser->map(function ($item) {
            return [
                'user_id' => $item->user_id,
                'user' => $item->user,
                'kompetensi' => $item->user->dataKaryawan->kompetensi,
                'status_karyawan' => $item->user->dataKaryawan->statusKaryawan, // Mengambil data kompetensi
              ];
        });
      }
      return response()->json(new DataResource(Response::HTTP_OK, 'Karyawan lain dengan jadwal yang sama berhasil di dapatkan', $data), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function getuserjadwal(User $user)
  {
    try {
    //   $datakaryawan = DataKaryawan::where('user_id', $user->id)->with('unitkerja')->first();

    //   if($datakaryawan->unitkerja->jenis_karyawan == 0) {

    //   }else {
    //       $jadwal = Jadwal::where('user_id', $user->id)->where('tgl_mulai', '>', Carbon::today())->with('shift')->get();
    //   }

    //   return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $jadwal), Response::HTTP_OK);
      $datakaryawan = DataKaryawan::where('user_id', $user->id)->with('unitkerja')->first();
      $hariLibur = HariLibur::all()->pluck('tanggal')->toArray();
      $nonShift = NonShift::where('id', 1)->first();
      // $start = Carbon::now()->startOfWeek();
      // $end = Carbon::now()->endOfWeek();
      // $startDate = Carbon::createFromFormat('Y-m-d', $start);
      // $endDate = Carbon::createFromFormat('Y-m-d', $end);
      // $startDate = Carbon::now('Asia/Jakarta')->startOfWeek();
      // $endDate = Carbon::now('Asia/Jakarta')->endOfWeek();
      $startDate = Carbon::now('Asia/Jakarta')->startOfWeek();
      $endDate = Carbon::now('Asia/Jakarta')->endOfWeek();

      // if($request->filled('tgl_mulai')) {
      //     $startDate = Carbon::createFromFormat('Y-m-d', $request->tgl_mulai, 'Asia/Jakarta');
      // }

      // if($request->filled('tgl_selesai')) {
      //     $endDate = Carbon::createFromFormat('Y-m-d', $request->tgl_selesai);
      // }

      // return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Jadwal berhasil didapatkan', $startDate), Response::HTTP_NOT_FOUND);


      if ($datakaryawan->unitkerja->jenis_karyawan == 0) {
        $date_range = $this->generateDateRange($startDate, $endDate);
        foreach ($date_range as $date) {
          $day_of_week = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

          if ($day_of_week == Carbon::SUNDAY) {
            // Libur pada hari Minggu
            // $user_schedule_array[$date] = [
            //     "id" => 0,
            //     "user_id" => Auth::user()->id,
            //     "tgl_mulai" => $day_of_week,
            //     "tgl_selesai" => $day_of_week,
            //     "shift_id" => 0,
            //     "created_at" => $day_of_week,
            //     "updated_at" => $day_of_week,
            //     "shift" => [
            //         "id" => 0,
            //         "nama" => "Libur Minggu",
            //         "jam_from" => "06:00:00",
            //         "jam_to" => "16:00:00",
            //         "deleted_at" => null,
            //         "created_at" => "2024-08-28T07:12:54.000000Z",
            //         "updated_at" => "2024-08-28T07:12:54.000000Z"
            //     ]
            // ];
          } elseif (isset($hariLibur[$date])) {
            $user_schedule_array[$date] = [
              'id' => $hariLibur[$date]->id,
              'nama' => $hariLibur[$date]->nama,
              'jam_from' => null,
              'jam_to' => null,
              'status' => 3 // libur besar
            ];
          } else if ($nonShift) {
            $user_schedule_array[$date] = [
              "id" => 0,
              "user_id" => Auth::user()->id,
              "tgl_mulai" => $date,
              "tgl_selesai" => $date,
              "shift_id" => 0,
              "created_at" => $date,
              "updated_at" => $date,
              "shift" => [
                "id" => 0,
                "nama" => "Jam Kerja",
                "jam_from" => $nonShift->jam_from,
                "jam_to" => $nonShift->jam_to,
                "deleted_at" => null,
                "created_at" => null,
                "updated_at" => null
              ]
            ];
          }
        }

        $result = array_values($user_schedule_array);
      } else {
        $result = Jadwal::where('user_id', $user->id)->whereBetween('tgl_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->with('shift')->orderBy('tgl_mulai', 'asc')->get();
        // return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $startDate->format('Y-m-d') . ' ' . $endDate->format('Y-m-d')), Response::HTTP_OK);
        if ($result->isEmpty()) {
          return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
      }

      return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal berhasil didapatkan', $result), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function changeschedule(Request $request)
  {
    try {
        $cektukarsebelum = TukarJadwal::whereNotNull('verifikator_1')->whereNotNull('verifikator_2')->where('user_pengajuan', Auth::user()->id)->get();

      if($cektukarsebelum->isNotEmpty()) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Anda masih memiliki pengajuan tukar jadwal yang belom terverifikasi'), Response::HTTP_NOT_ACCEPTABLE);
      }

      $cek = Jadwal::where('id', $request->jadwal_id_ditukar)->with('userDitukar')->first();
      if (!$cek) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      $jadwalawal = Jadwal::where('id', $request->jadwal_id_penukar)->first();

      if (!$jadwalawal) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      if ($cek->user_id == $jadwalawal->user_id) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tidak bisa menukar jadwal anda sendiri'), Response::HTTP_NOT_ACCEPTABLE);
      }

      if ($cek->tgl_mulai == $jadwalawal->tgl_mulai && $cek->shift_id == $jadwalawal->shift_id) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Tidak bisa menukar jadwal yang sama'), Response::HTTP_NOT_ACCEPTABLE);
      }

      $kategori = 1; // default Tukar Shift

      if ($cek->shift_id == null) {
        $kategori = 2; // diubah menjadi Tukar Libur
      }

      $tukarjadwal = TukarJadwal::create([
        'user_pengajuan' => $jadwalawal->user_id,
        'jadwal_pengajuan' => $jadwalawal->id,
        'user_ditukar' => $cek->user_id,
        'jadwal_ditukar' => $cek->id,
        'status_penukaran_id' => 1, //Menunggu
        'kategori_penukaran_id' => $kategori,
        'acc_user_ditukar' => 1
      ]);

      $notifikasi = Notifikasi::create([
        'kategori_notifikasi_id' => 2,
        'user_id' => Auth::user()->id,
        'message' => 'Pengajuan tukar jadwal ' . Auth::user()->nama . ' terkirim',
        'is_read' => 0,
        'is_verifikasi' => 1,
      ]);

      Notifikasi::create([
        'kategori_notifikasi_id' => 2,
        'user_id' => $cek->user_id,
        'message' => Auth::user()->nama . ' mengajukan tukar jadwal dengan anda',
        'is_read' => 0,
        'is_verifikasi' => 1,
      ]);

      Notifikasi::create([
        'kategori_notifikasi_id' => 2,
        'user_id' => 1,
        'message' => Auth::user()->nama . ' mengajukan tukar jadwal dengan ' . $cek->userDitukar->nama,
        'is_read' => 0,
        'is_verifikasi' => 1,
      ]);

      return response()->json(new DataResource(Response::HTTP_OK, 'Permintaan tukar jadwal berhasil terkirim', $tukarjadwal), Response::HTTP_OK);

    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getjadwalditukar(Jadwal $jadwal)
  {
    try {
      if ($jadwal->shift_id == 0) {
        $karyawanA = Jadwal::where('user_id', Auth::user()->id)
          ->where('shift_id', 0)
          ->where('tgl_mulai', '>=', date('Y-m-d'))
          ->get();

        $validSchedules = [];

        foreach ($karyawanA as $schedule) {
          $karyawanB = Jadwal::where('tgl_mulai', $schedule->tgl_mulai)
            ->where('shift_id', 0)
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
        //   ->whereIn('shift_id', 1)
          ->where('shift_id', '!=', 0)
          ->where('tgl_mulai', '>=', date('Y-m-d'))
        //   ->where('tgl_mulai', $jadwal->tgl_mulai)
          ->with('shift')
          ->get();

        return response()->json(new DataResource(Response::HTTP_OK, 'Jadwal ditukar', $schedule), Response::HTTP_OK);
      }
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  private function generateDateRange($start_date, $end_date)
  {
    $dates = [];
    for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
      $dates[] = $date->format('Y-m-d');
    }
    return $dates;
  }
}
