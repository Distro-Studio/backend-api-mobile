<?php

namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Helpers\UserActiveHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\Jadwal;
use App\Models\LokasiKantor;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

        $checkuser = UserActiveHelper::checkActive(User::where('id', Auth::user()->id)->first());
        if (!$checkuser) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'User belum aktif'), Response::HTTP_NOT_ACCEPTABLE);

        }

        $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
        $jadwal = Jadwal::where('user_id', Auth::user()->id)->where('tgl_mulai', date('Y-m-d'))->with('shift')->first();
        if (!$jadwal) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Jadwal tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
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
            // $status = "Karyawan terlambat $differenceInMinutes menit.";
            $status = "terlambat";
        } elseif ($end->eq($start)) {
            $differenceInMinutes = 0;
            // $status = "Karyawan tepat waktu.";
            $status = "tepat waktu";
        } else {
            $differenceInMinutes = $start->diffInMinutes($end);
            $status = "lebih awal";
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

            if($checkpresensi){
                return response()->json(new DataResource(Response::HTTP_IM_USED, 'Presennsi sudah dilakukan sebelumnya', $checkpresensi), Response::HTTP_IM_USED);
            }

            try{
                $response = Http::asForm()->post('http://127.0.0.1:8001/api/login',[
                    'username' => 'usermobilerski',
                    'password' => '12345678'
                ]);
                $logininfo = $response->json();
                $token = $logininfo['data']['token'];
                $file = $request->file('foto');

                $responseupload = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->asMultipart()->post('http://127.0.0.1:8001/api/upload',[
                    'filename' => 'Check in '. Auth::user()->name,
                    'file' => fopen($file->getRealPath(), 'r'),
                    'kategori' => 'Umum'
                ]);

                $uploadinfo = $responseupload->json();
                $dataupload = $uploadinfo['data'];

                $logout = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->post('http://127.0.0.1:8001/api/logout');

                $saveberkas = Berkas::create([
                    'user_id' => Auth::user()->id,
                    'nama' => Auth::user()->name,
                    'kategori' => 'System',
                    'path' => $dataupload['path'],
                    'tgl_upload' => date('Y-m-d'),
                    'nama_file' => $dataupload['nama_file'],
                    'ext' => $dataupload['ext'],
                    'size' => $dataupload['size'],
                    'file_id' => $dataupload['id_file']['id']
                ]);



                $presensi = Presensi::create([
                    'user_id' => Auth::user()->id,
                    'data_karyawan_id' => $datakaryawan->id,
                    'jadwal_id' => $jadwal->id,
                    'jam_masuk' => date('Y-m-d H:i:s'),
                    'lat' => $request->lat,
                    'long' => $request->long,
                    'foto_masuk' => $saveberkas->id,
                    'presensi' => 'hadir',
                    'kategori' => $status.' '.$differenceInMinutes.' Menit',
                ]);

                return response()->json(new DataResource(Response::HTTP_OK, 'Presensi berhasil dilakukan', $presensi), Response::HTTP_OK);
            } catch (\Exception $e){
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Anda diluar radius kantor'), Response::HTTP_NOT_ACCEPTABLE);
        }
    }

}
