<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\Lembur;
use App\Models\NonShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LemburController extends Controller
{
  private function timeToSeconds($time)
  {
    $time = Carbon::parse($time);

    // Calculate the total seconds from the start of the day (00:00:00)
    $totalSeconds = $time->diffInSeconds(Carbon::today());

    return $totalSeconds;
  }

  public function getstatistik()
  {
    try {
      $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
      $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth()->toDateString();
      $query = Lembur::query();
      $query->where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

      $totallembur = $query->count();
      $data = $query->get();
       $time = Carbon::parse();
      $totalwaktu = 0;
      if($data->isNotEmpty()){
        foreach ($data as $d) {
            $seconds = $d->durasi;
            $totalwaktu += $seconds;
          }
      } else {
          $seconds = 0;
      }
      
      // $totallembur = Lembur::where('user_id', Auth::user()->id)->where('status_lembur_id', 3)->whereBetween('tgl_pengajuan', [$tgl_mulai, $tgl_selesai])->count();

      $data = [
        'total_lembur' => $totallembur,
        'total_waktu' => $seconds
      ];

      return response()->json(new DataResource(Response::HTTP_OK, 'Statistik lembur berhasil didapatkan', $data), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getriwayat(Request $request)
  {
    $offset = 4;
    if ($request->filled('offset')) {
      $offset = $request->offset;
    }

    try {
      $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
      $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth()->toDateString();
      $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
      $query = Lembur::query();
      // $query->where('user_id', Auth::user()->id)->where('status_lembur_id', 3)->whereBetween('tgl_pengajuan', [$tgl_mulai, $tgl_selesai]);

      if ($datakaryawan->unitkerja->jenis_karyawan == 1) {
        $query->with(['jadwal.shift']);
      }
      $query->where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

      $datasementara = $query->with('user')->get();

      if ($datakaryawan->unitkerja->jenis_karyawan == 1) {
        $data = $datasementara->map(function ($item) {
          return [
            "id" => 2,
            "user_id" => Auth::user()->id,
            "jadwal_id" => $item->jadwal_id,
            "durasi" => $item->durasi,
            "catatan" => $item->catatan,
            "created_at" => $item->created_at,
            "updated_at" => $item->updated_at,
            'jadwal' => [
              'id' => $item->jadwal->id,
              'tgl_mulai' => $item->jadwal->tgl_mulai,
              'tgl_selesai' => $item->jadwal->tgl_selesai,
              'jam_from' => $item->jadwal->shift->jam_from, // Ambil dari relasi shift
              'jam_to' => $item->jadwal->shift->jam_to,     // Ambil dari relasi shift
            ],
            "user" => [
              "id" => 42,
              "nama" => "User 40",
              "email_verified_at" => null,
              "password" => "$2y$12$\/HuV7b3xaXv61981LsSWi.YKLElarjxHGKTagZCarA1.m79BNL.Iq",
              "role_id" => null,
              "data_karyawan_id" => 42,
              "foto_profil" => null,
              "data_completion_step" => 0,
              "status_aktif" => 2,
              "remember_token" => null,
              "created_at" => "2024-08-28T05:47:37.000000Z",
              "updated_at" => "2024-08-28T05:47:37.000000Z"
            ]
          ];
        });
      } else {

        $data = $datasementara->map(function ($item) {
          $nonshift = NonShift::where('id', 1)->first();
          $jamMasuk = Carbon::parse($nonshift->jam_from);
          $jamKeluar = Carbon::parse($nonshift->jam_to);
          return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'jadwal' => [
              'id' => null,
              'tgl_mulai' => null,
              'tgl_selesai' => null,
              'jam_from' => $jamMasuk, // Ambil dari relasi shift
              'jam_to' => $jamKeluar,     // Ambil dari relasi shift
            ],
            // Atribut lainnya...
          ];
        });
      }


      //   return response()->json(new DataResource(Response::HTTP_OK, 'Riwayat lembur berhasil didapatkan', $data), Response::HTTP_OK);
      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => 'Riwayat lembur berhasil didapatkan',
        'data' => $data
      ], Response::HTTP_OK);

    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
