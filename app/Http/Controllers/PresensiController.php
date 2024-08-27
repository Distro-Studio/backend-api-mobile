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
            $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
            if (!$jadwal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }
            $jadwalid = $jadwal->id;
            $start = Carbon::createFromFormat('H:i:s', $jadwal->shift->jam_from, 'Asia/Jakarta');
        }else{
            $nonshift = NonShift::where('id', 1)->first();
            $jamMasuk = Carbon::parse($nonshift->jam_from);
            $jamKeluar = Carbon::parse($nonshift->jam_to);
            $waktuSekarang = Carbon::now();
            if (!$waktuSekarang->between($jamMasuk, $jamKeluar)) {
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
        } else {
            $differenceInMinutes = $start->diffInMinutes($end);
            $status = 3; //ABSEN
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
                $dataupload = StorageFileHelper::uploadToServer($request, Str::random(12), 'foto');

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


            $checkpresensi = Presensi::where('user_id', Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->where('jam_keluar', NULL)->first();

            if(!$checkpresensi){
                return response()->json(new DataResource(Response::HTTP_IM_USED, 'Presensi belum dilakukan', $checkpresensi), Response::HTTP_IM_USED);
            }

            try{
                // $presensisebelum = Presensi::where('user_id', Auth::user()->id)->where('jam_keluar', NULL)->update(['presensi' => 0]);

                $dataupload = StorageFileHelper::uploadToServer($request, 'Check out '. Auth::user()->nama, 'foto');

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

                $startTime = Carbon::parse($checkpresensi->jam_masuk);
                $endTime = Carbon::parse($time);
                $duration = $startTime->diff($endTime);


                $checkpresensi->jam_keluar = $time;
                $checkpresensi->durasi = $duration->s;
                $checkpresensi->latkeluar = $request->lat;
                $checkpresensi->longkeluar = $request->long;
                // $checkpresensi->presensi = 1;
                $checkpresensi->foto_keluar = $saveberkas->id;
                $checkpresensi->save();

                $activity = ActivityLog::create([
                    'activity' => 'Keluar',
                    'user_id' => Auth::user()->id,
                    'kategori_activity_id' => 1
                ]);

                return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', $checkpresensi), Response::HTTP_OK);
            } catch (\Exception $e){
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Anda diluar radius kantor'), Response::HTTP_NOT_ACCEPTABLE);
        }

    }

    public function getactivity()
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
            $presensiBulanIni = Presensi::where('user_id', Auth::user()->id)
                ->whereBetween('created_at',[$starofweek, $endofweek])
                ->orderBy('jam_masuk')
                ->get();

                $aktivitasPresensi = [];
                foreach ($presensiBulanIni as $presensi) {
                  if ($presensi->jam_masuk) {
                    $aktivitasPresensi[] = [
                      'presensi' => 'Masuk',
                      'tanggal' => Carbon::parse($presensi->jam_masuk)->format('Y-m-d'),
                      'jam' =>  Carbon::parse($presensi->jam_masuk)->format('H:i:s'),
                    ];
                  }
                  if ($presensi->jam_keluar) {
                    $aktivitasPresensi[] = [
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



}
