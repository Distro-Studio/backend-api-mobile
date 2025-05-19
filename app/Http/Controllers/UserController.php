<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
  public function checkuser()
  {
    $masastr = null;
    $masasip = null;
    $checkuser = DataKaryawan::where('user_id', Auth::user()->id)->first();
    if (!$checkuser) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'User tidak ditemukan'), Response::HTTP_NOT_FOUND);
    }

    if($checkuser->masa_berlaku_str != null){
        $masaBerlaku = Carbon::parse($checkuser->masa_berlaku_str);

        // Tentukan tanggal mulai untuk pemberitahuan 7 bulan sebelum masa berlaku berakhir
        $alertStartDate = $masaBerlaku->copy()->subMonths(7);

        // Tentukan tanggal akhir (masa berlaku habis)
        $alertEndDate = $masaBerlaku;

        // Cek setiap bulan antara alertStartDate dan alertEndDate
        $currentDate = Carbon::now();

        if ($currentDate->between($alertStartDate, $alertEndDate)) {
            // Tampilkan alert jika sekarang adalah bulan yang sesuai
            $masastr = 'Masa berlaku STR anda akan habis pada tanggal ' . $masaBerlaku->format('d-m-Y') . '. Silahkan segera perbarui STR anda';
        }
    }

    if($checkuser->masa_berlaku_sip != null){
        $masaBerlakusip = Carbon::parse($checkuser->masa_berlaku_sip);

        // Tentukan tanggal mulai untuk pemberitahuan 3 bulan sebelum masa berlaku berakhir
        $alertStartDatesip = $masaBerlakusip->copy()->subMonths(3);

        // Tentukan tanggal akhir (masa berlaku habis)
        $alertEndDatesip = $masaBerlakusip;

        // Cek setiap bulan antara alertStartDatesip dan alertEndDatesip
        $currentDatesip = Carbon::now();

        if ($currentDatesip->between($alertStartDatesip, $alertEndDatesip)) {
            // Tampilkan alert jika sekarang adalah bulan yang sesuai
            $masasip = 'Masa berlaku SIP anda akan habis pada tanggal ' . $masaBerlakusip->format('d-m-Y') . '. Silahkan segera perbarui SIP anda';
        }
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'User berhasil di dapatkan',
      'data' =>
        [
          'user' => Auth::user()->makeHidden('password'),
          'unit_kerja' => Auth::user()->dataKaryawan->unitkerja,
          'masa_str' => $masastr,
          'masa_sip' => $masasip,
        ]
    ], Response::HTTP_OK);
  }
}
