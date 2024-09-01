<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\Pengumuman;
use App\Models\RiwayatPerubahan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GetListController extends Controller
{
  public function getlistagama()
  {
    try {
      $agama = KategoriDarah::select('id', 'label')->get();
      return response()->json(new DataResource(Response::HTTP_OK, 'List agama berhasil didapatkan', $agama), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
  public function getlistgoldar()
  {
    try {
      $goldar = KategoriDarah::select('id', 'label')->get();
      return response()->json(new DataResource(Response::HTTP_OK, 'List golongan darah berhasil didapatkan', $goldar), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getkaryawanunitkerja(Request $request)
  {
    try {
      $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
      $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

      $data = DataKaryawan::select('unit_kerja_id')->where('user_id', Auth::user()->id)->first();
      // $query = DataKaryawan::select('id', 'user_id', 'kompetensi_id')->where('unit_kerja_id', $data->unit_kerja_id)->where('user_id', '!=', Auth::user()->id)->with('user', 'kompetensi', ['jadwal' => function($query) use ($startDate, $endDate) {
      //     if ($startDate && $endDate) {
      //         $query->whereBetween('tgl_mulai', [$startDate, $endDate]);
      //     }
      // }]);
      $query = DataKaryawan::select('id', 'user_id', 'kompetensi_id')
        ->where('unit_kerja_id', $data->unit_kerja_id)
        ->where('user_id', '!=', Auth::user()->id)
        ->with([
          'user',
          'kompetensi',
          'jadwal' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
              $query->whereBetween('tgl_mulai', [$startDate, $endDate]);
            }
          }
        ]);
      $filters = $request->all();
      if (isset($filters['status_karyawan'])) {
        $statusKaryawan = $filters['status_karyawan'];
        $query->whereHas('status_karyawans', function ($karyawan) use ($statusKaryawan) {
          if (is_array($statusKaryawan)) {
            $karyawan->whereIn('id', $statusKaryawan);
          } else {
            $karyawan->where('id', '=', $statusKaryawan);
          }
        });
      }
      $users = $query->get();

      // $user->is_libur = false;
      $today = Carbon::now()->format('Y-m-d');
      $users = $users->map(function ($user) use ($today) {
        $isLibur = $user->jadwal->where('tgl_mulai', $today)
          ->where('shift_id', null)
          ->isNotEmpty();

        $user->is_libur = $isLibur ? true : false;
        return $user;
      });

      return response()->json(new DataResource(Response::HTTP_OK, 'User berhasil didapatkan', $users), Response::HTTP_OK);

    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getpengumuman(Request $request)
  {
    try {
      if ($request->limit == 0) {
        $pengumuman = Pengumuman::all();
      } else {
        $pengumuman = Pengumuman::take($request->limit)->get();
      }

      if ($pengumuman->isEmpty()) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      return response()->json(new DataResource(Response::HTTP_OK, 'Pengumuman berhasil didapatkan', $pengumuman), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getlistnotifikasi()
  {
    $user = Auth::user();
    $notifikasi = Notifikasi::where('user_id', $user->id)
      ->orderBy('is_read', 'asc')
      ->orderBy('created_at', 'desc')
      ->get();

    if ($notifikasi->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data notifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $formattedData = $notifikasi->map(function ($item) {
      return [
        'id' => $item->id,
        'kategori_notifikasi' => $item->kategori_notifikasis,
        'user' => $item->users,
        'message' => $item->message,
        'is_read' => $item->is_read,
        'created_at' => $item->created_at,
        'updated_at' => $item->updated_at
      ];
    });

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Data notifikasi berhasil ditampilkan.',
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function getriwayatperubahan()
  {
    $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
    $riwayat = RiwayatPerubahan::where('data_karyawan_id', $datakaryawan->id)->get();
    if (!$riwayat) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat perubahan tidak ditemukan'), Response::HTTP_NOT_FOUND);
    }

    return response()->json(new DataResource(Response::HTTP_OK, 'Riwayat perubahan ditemukan', $riwayat), Response::HTTP_OK);
  }
}
