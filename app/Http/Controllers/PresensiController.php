<?php

namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Helpers\StorageFileHelper;
// use App\Helpers\UploadFileHelper;
use App\Helpers\UserActiveHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\ActivityLog;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\Jadwal;
use App\Models\LokasiKantor;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;


class PresensiController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required'
        ], [
            'lat.required' => 'Latitude harus diisi',
            'long.required' => 'Longitude harus diisi'
        ]);

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }


        // $checkuser = UserActiveHelper::checkActive(User::where('id', Auth::user()->id)->first());
        // if (!$checkuser) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'User belum aktif'), Response::HTTP_NOT_ACCEPTABLE);

        // }

        $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
        if ($datakaryawan->unitkerja->jenis_karyawan == 1) {
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


            // $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
            if (!$jadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }
            $jadwalid = $jadwal->id;
            $start = Carbon::createFromFormat('H:i:s', $jadwal->shift->jam_from, 'Asia/Jakarta');
        }else{
            // $nonshift = NonShift::where('id', 1)->first();
            // $jamMasuk = Carbon::parse($nonshift->jam_from);
            // $jamKeluar = Carbon::parse($nonshift->jam_to);
            // $waktuSekarang = Carbon::now();
            // if (!$waktuSekarang->between($jamMasuk, $jamKeluar)) {
            //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);

            // }
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

            $jadwalid = null;
            $start = Carbon::createFromFormat('H:i:s', $nonshift->jam_from, 'Asia/Jakarta');
        }
        // $start = Carbon::createFromTimeString($jadwal->shift->jam_from, 'Asia/Jakarta');
        // $start = Carbon::createFromFormat('H:i:s', $jadwal->shift->jam_from, 'Asia/Jakarta');
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
            // $status = "Karyawan terlambat $differenceInMinutes menit.";
            $status = 2; // TERLAMBAT
        } elseif ($end->eq($start)) {
            $differenceInMinutes = 0;
            // $status = "Karyawan tepat waktu.";
            $status = 1; //TEPAT WAKTU
        } else if($end->lt($start)){
            $differenceInMinutes = 0;
            // $status = "Karyawan tepat waktu.";
            $status = 1; //TEPAT WAKTU
        }else {
            $differenceInMinutes = $start->diffInMinutes($end);
            $status = 4; //ABSEN
        }

        // return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'sdad'), Response::HTTP_NOT_FOUND);
        //cek lokasi user
        $lokasi = LokasiKantor::where('id', 1)->first();
        $latoffice = $lokasi->lat;
        $longoffice = $lokasi->long;
        $latuser = $request->lat;
        $longuser = $request->long;
        $radius = $lokasi->radius;



        $distance = LocationHelper::haversineGreatCircleDistance(
            $latoffice,
            $longoffice,
            $latuser,
            $longuser
        );

        if ($distance <= $radius) {

            $checkpresensi = Presensi::where('user_id', Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->where('jam_keluar', NULL)->first();

            if($checkpresensi){
                return response()->json(new DataResource(Response::HTTP_BAD_REQUEST, 'Presennsi sudah dilakukan sebelumnya', $checkpresensi), Response::HTTP_BAD_REQUEST);
            }

            try{
                // $presensisebelum = Presensi::where('user_id', Auth::user()->id)->where('jam_keluar', NULL)->update(['presensi' => 0]);
                $dataupload = StorageFileHelper::uploadToServer($request, Str::random(12).Carbon::now()->timestamp, 'foto');

                $saveberkas = Berkas::create([
                    'user_id' => Auth::user()->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => Auth::user()->nama,
                    'kategori_berkas_id' => 3,
                    'status_berkas_id' => 2,
                    'path' => $dataupload['path'],
                    'tgl_upload' => date('Y-m-d'),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);



                $presensi = Presensi::create([
                    'user_id' => Auth::user()->id,
                    'data_karyawan_id' => $datakaryawan->id,
                    'jadwal_id' => $jadwalid,
                    'jam_masuk' => date('Y-m-d H:i:s'),
                    'lat' => $request->lat,
                    'long' => $request->long,
                    'foto_masuk' => $saveberkas->id,
                    'status_presensi_id' => 1,
                    'kategori_presensi_id' => $status,
                ]);

                $checkinTime = Carbon::now();
                if($jadwalid) {
                    $jadwalTime = Carbon::parse($jadwal->shift->jam_from);
                    if ($checkinTime->greaterThan($jadwalTime)) {
                        $datakaryawan->status_reward_presensi = 0;
                        $datakaryawan->save();
                    }
                }else {
                    if($checkinTime->greaterThan($jamMasuk)) {
                        $datakaryawan->status_reward_presensi = 0;
                        $datakaryawan->save();
                    }
                }

                $activity = ActivityLog::create([
                    'activity' => 'Masuk',
                    'user_id' => Auth::user()->id,
                    'kategori_activity_id' => 1 //PRESENSI
                ]);

                return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', $presensi), Response::HTTP_OK);
            } catch (\Exception $e){
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Anda diluar radius kantor'), Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function checkoutpresensi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required'
        ], [
            'lat.required' => 'Latitude harus diisi',
            'long.required' => 'Longitude harus diisi'
        ]);

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }



        //cek lokasi user
        $lokasi = LokasiKantor::where('id', 1)->first();
        $latoffice = $lokasi->lat;
        $longoffice = $lokasi->long;
        $latuser = $request->lat;
        $longuser = $request->long;
        $radius = $lokasi->radius;

        $distance = LocationHelper::haversineGreatCircleDistance(
            $latoffice,
            $longoffice,
            $latuser,
            $longuser
        );

        if ($distance <= $radius) {

            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $now = Carbon::now();
            $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
            if($datakaryawan->unitkerja->jenis_karyawan == 1){
                $checkpresensi = Presensi::where('user_id', Auth::user()->id)
                    ->whereNull('jam_keluar')
                    ->whereHas('jadwal', function($query) use ($now) {
                        $query->whereDate('tgl_mulai', '<=', $now)
                            ->whereDate('tgl_selesai', '>=', $now);
                    })->with(['jadwal.shift'])
                    ->first();
                $timetoout = $checkpresensi->jadwal->shift->jam_to;
            }else {
                $checkpresensi = Presensi::where('user_id', Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->where('jam_keluar', NULL)->first();
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
                $timetoout = $nonshift->jam_to;
            }

            if(!$checkpresensi){
                return response()->json(new DataResource(Response::HTTP_IM_USED, 'Presensi belum dilakukan', $checkpresensi), Response::HTTP_IM_USED);
            }

            try{
                // $presensisebelum = Presensi::where('user_id', Auth::user()->id)->where('jam_keluar', NULL)->update(['presensi' => 0]);

                $dataupload = StorageFileHelper::uploadToServer($request, Str::random(12).Carbon::now()->timestamp, 'foto');

                // $saveberkas = Berkas::create([
                //     'user_id' => Auth::user()->id,
                //     'nama' => Auth::user()->name,
                //     'kategori' => 'System',
                //     'path' => $dataupload['path'],
                //     'tgl_upload' => date('Y-m-d'),
                //     'nama_file' => $dataupload['nama_file'],
                //     'ext' => $dataupload['ext'],
                //     'size' => $dataupload['size'],
                //     'file_id' => $dataupload['id_file']['id']
                // ]);
                $saveberkas = Berkas::create([
                    'user_id' => Auth::user()->id,
                    'file_id' => $dataupload['id_file']['id'],
                    'nama' => Auth::user()->nama,
                    'kategori_berkas_id' => 3,
                    'status_berkas_id' => 2,
                    'path' => $dataupload['path'],
                    'tgl_upload' => date('Y-m-d'),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                ]);

                //face recognition
                $face = true;

                if(!$face)
                {
                    return response()->json(new DataResource(Response::HTTP_NOT_ACCEPTABLE, 'Wajah berbeda menurut sistem', [
                        'lat' => $latuser,
                        'long' => $longuser,
                        'id_foto' => $saveberkas->id,
                    ]), Response::HTTP_NOT_ACCEPTABLE);
                }

                $time = date('Y-m-d H:i:s');

                // $startTime = Carbon::parse($checkpresensi->jam_masuk);
                // $endTime = Carbon::parse($time);
                // $duration = $startTime->diff($endTime);


                // $checkpresensi->jam_keluar = $time;
                // $checkpresensi->durasi = $duration->s;
                // $checkpresensi->latkeluar = $request->lat;
                // $checkpresensi->longkeluar = $request->long;

                $startTime = Carbon::parse($checkpresensi->jam_masuk);
                $endTime = Carbon::parse($time);

                $checkpresensi->jam_keluar = $time;
                $durationInSeconds = $startTime->diffInSeconds($checkpresensi->jam_keluar);
                $checkpresensi->durasi = $durationInSeconds;
                $checkpresensi->latkeluar = $request->lat;
                $checkpresensi->longkeluar = $request->long;
                // $checkpresensi->presensi = 1;
                $checkpresensi->foto_keluar = $saveberkas->id;
                $checkpresensi->save();

                $chekoutTime = Carbon::now();
                $outTime = Carbon::parse($timetoout);

                if($chekoutTime->lessThan($outTime)) {
                    DataKaryawan::where('user_id', Auth::user()->id)->update(['status_reward_presensi' => 0]);
                }

                $activity = ActivityLog::create([
                    'activity' => 'Keluar',
                    'user_id' => Auth::user()->id,
                    'kategori_activity_id' => 1
                ]);

                return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', $checkpresensi), Response::HTTP_OK);
            } catch (\Exception $e){
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getLine()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Anda diluar radius kantor'), Response::HTTP_NOT_ACCEPTABLE);
        }

    }

    public function getactivity(Request $request)
    {
        try {
            //code...
            // $activity = ActivityLog::where('user_id', Auth::user()->id)
            //     ->where('kategori', 'Presensi')
            //     ->select('activity', 'created_at')
            //     ->get();

            // if ($activity->isEmpty()) {
            //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data activity log tidak ditemukan'), Response::HTTP_NOT_FOUND);
            // }

            // // Pisahkan tanggal dan jam
            // $formattedActivity = $activity->map(function ($item) {
            //     return [
            //         'activity' => $item->activity,
            //         'tanggal' => $item->tanggal,
            //         'jam' => $item->jam,
            //     ];
            // });

            // return response()->json(new DataResource(Response::HTTP_OK, 'Data activity log', $formattedActivity), Response::HTTP_OK);
            $starofweek = Carbon::now()->startOfMonth();
            $endofweek = Carbon::now()->endOfMonth();
            $take = 14;

            if($request->tgl_mulai != null && $request->tgl_selesai != null){
                $starofweek = Carbon::parse($request->tgl_mulai)->format('Y-m-d');
                $endofweek = Carbon::parse($request->tgl_selesai)->format('Y-m-d');
            }

            if($request->limit) {
                $take = $request->limit;
            }
            $presensiBulanIni = Presensi::where('user_id', Auth::user()->id)
                ->whereBetween('created_at',[$starofweek, $endofweek])
                ->orderBy('jam_masuk')
                ->take($take)->get();

                $aktivitasPresensi = [];
                foreach ($presensiBulanIni as $presensi) {
                  if ($presensi->jam_masuk) {
                    $aktivitasPresensi[] = [
                      'id' => $presensi->id,
                      'presensi' => 'Masuk',
                      'tanggal' => Carbon::parse($presensi->jam_masuk)->format('Y-m-d'),
                      'jam' =>  Carbon::parse($presensi->jam_masuk)->format('H:i:s'),
                    ];
                  }
                  if ($presensi->jam_keluar) {
                    $aktivitasPresensi[] = [
                      'id' => $presensi->id,
                      'presensi' => 'Keluar',
                      'tanggal' => Carbon::parse($presensi->jam_keluar)->format('Y-m-d'),
                      'jam' =>  Carbon::parse($presensi->jam_keluar)->format('H:i:s'),
                    ];
                  }
                }

            return response()->json(new DataResource(Response::HTTP_OK, 'Aktivitas berhasil didapatkan', $aktivitasPresensi), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getdetailpresensi(Request $request)
    {
        try {
            // $presensi = Presensi::where('id', $request->id)->first();
            $presensiHariIni = Presensi::with([
                'user',
                'jadwal.shift',
                'datakaryawan.unitkerja',
                'kategori_presensis'
            ])
                ->where('id', $request->id)
                ->first();

            if (!$presensiHariIni) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data presensi karyawan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }

            $fotoMasukBerkas = Berkas::where('id', $presensiHariIni->foto_masuk)->first();
            $fotoKeluarBerkas = Berkas::where('id', $presensiHariIni->foto_keluar)->first();

            $baseUrl = env('STORAGE_SERVER_DOMAIN'); // Ganti dengan URL domain Anda

            $fotoMasukExt = $fotoMasukBerkas ? StorageFileHelper::getExtensionFromMimeType($fotoMasukBerkas->ext) : null;
            $fotoMasukUrl = $fotoMasukBerkas ? $baseUrl . $fotoMasukBerkas->path : null;

            $fotoKeluarExt = $fotoKeluarBerkas ? StorageFileHelper::getExtensionFromMimeType($fotoKeluarBerkas->ext) : null;
            $fotoKeluarUrl = $fotoKeluarBerkas ? $baseUrl . $fotoKeluarBerkas->path : null;

            // Ambil data lokasi kantor
            $lokasiKantor = LokasiKantor::find(1);

            // Ambil data jadwal non-shift jika jenis_karyawan = false
            $jadwalNonShift = null;
            $jenisKaryawan = $presensiHariIni->users->datakaryawan->unitkerja->jenis_karyawan ?? null;
            if ($jenisKaryawan === 0) {
                $jamMasukDate = Carbon::parse($presensiHariIni->jam_masuk)->format('l');
                $hariNamaIndonesia = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ][$jamMasukDate] ?? 'Senin';
                $jadwalNonShift = NonShift::where('nama', $hariNamaIndonesia)->first();
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Detail data presensi karyawan '{$presensiHariIni->user->nama}' berhasil ditampilkan.",
                'data' => [
                    'id' => $presensiHariIni->id,
                    'user' => [
                        'id' => $presensiHariIni->user->id,
                        'nama' => $presensiHariIni->user->nama,
                        'username' => $presensiHariIni->user->username,
                        'email_verified_at' => $presensiHariIni->user->email_verified_at,
                        'data_karyawan_id' => $presensiHariIni->user->data_karyawan_id,
                        'foto_profil' => $presensiHariIni->user->foto_profil,
                        'data_completion_step' => $presensiHariIni->user->data_completion_step,
                        'status_aktif' => $presensiHariIni->user->status_aktif,
                        'created_at' => $presensiHariIni->user->created_at,
                        'updated_at' => $presensiHariIni->user->updated_at,
                    ],
                    'unit_kerja' => $presensiHariIni->datakaryawan->unitkerja,
                    'data_presensi' => [
                        'jadwal_shift' => $presensiHariIni->jadwal ? [
                            'id' => $presensiHariIni->jadwal->id,
                            'tgl_mulai' => $presensiHariIni->jadwal->tgl_mulai,
                            'tgl_selesai' => $presensiHariIni->jadwal->tgl_selesai,
                            'shift' => $presensiHariIni->jadwal->shifts,
                        ] : null,
                        'jadwal_non_shift' => $jadwalNonShift ? [
                            'id' => $jadwalNonShift->id,
                            'nama' => $jadwalNonShift->nama,
                            'jam_from' => $jadwalNonShift->jam_from,
                            'jam_to' => $jadwalNonShift->jam_to,
                            'deleted_at' => $jadwalNonShift->deleted_at,
                            'created_at' => $jadwalNonShift->created_at,
                            'updated_at' => $jadwalNonShift->updated_at,
                        ] : null,
                        'jam_masuk' => $presensiHariIni->jam_masuk,
                        'jam_keluar' => $presensiHariIni->jam_keluar,
                        'durasi' => $presensiHariIni->durasi,
                        'lokasi_kantor' => [
                            'id' => $lokasiKantor->id,
                            'alamat' => $lokasiKantor->alamat,
                            'lat' => $lokasiKantor->lat,
                            'long' => $lokasiKantor->long,
                            'radius' => $lokasiKantor->radius,
                        ],
                        'lat_masuk' => $presensiHariIni->lat,
                        'long_masuk' => $presensiHariIni->long,
                        'lat_keluar' => $presensiHariIni->latkeluar,
                        'long_keluar' => $presensiHariIni->longkeluar,
                        'foto_masuk' => $fotoMasukBerkas ? [
                            'id' => $fotoMasukBerkas->id,
                            'user_id' => $fotoMasukBerkas->user_id,
                            'file_id' => $fotoMasukBerkas->file_id,
                            'nama' => $fotoMasukBerkas->nama,
                            'nama_file' => $fotoMasukBerkas->nama_file,
                            'path' => env('URL_STORAGE').$fotoMasukUrl,
                            'ext' => $fotoMasukBerkas->ext,
                            'size' => $fotoMasukBerkas->size,
                        ] : null,
                        'foto_keluar' => $fotoKeluarBerkas ? [
                            'id' => $fotoKeluarBerkas->id,
                            'user_id' => $fotoKeluarBerkas->user_id,
                            'file_id' => $fotoKeluarBerkas->file_id,
                            'nama' => $fotoKeluarBerkas->nama,
                            'nama_file' => $fotoKeluarBerkas->nama_file,
                            'path' => env('URL_STORAGE').$fotoKeluarUrl,
                            'ext' => $fotoKeluarBerkas->ext,
                            'size' => $fotoKeluarBerkas->size,
                        ] : null,
                        'kategori_presensi' => $presensiHariIni->kategori_presensis,
                        'created_at' => $presensiHariIni->created_at,
                        'updated_at' => $presensiHariIni->updated_at
                    ]
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
