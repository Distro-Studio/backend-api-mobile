<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\DetailGaji;
use App\Models\Diklat;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\KategoriPendidikan;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use App\Models\Pengumuman;
use App\Models\RiwayatPerubahan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
      if (isset($filters['status'])) {
        $statusKaryawan = $filters['status'];
        $query->whereHas('statusKaryawan', function ($karyawan) use ($statusKaryawan) {
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
        $pengumuman = Pengumuman::whereJsonContains('user_id', Auth::user()->id)->where('tgl_mulai', '<=', Carbon::now())->where('tgl_berakhir', '>=', Carbon::now())->get();
      } else {
        $pengumuman = Pengumuman::whereJsonContains('user_id', Auth::user()->id)->where('tgl_mulai', '<=', Carbon::now())->where('tgl_berakhir', '>=', Carbon::now())->take($request->limit)->get();
      }

      if ($pengumuman->isEmpty()) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengumuman tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      return response()->json(new DataResource(Response::HTTP_OK, 'Pengumuman berhasil didapatkan', $pengumuman), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getlistnotifikasi()
  {
    $user = Auth::user();
    $notifikasi = Notifikasi::whereJsonContains('user_id', $user->id)
      ->orderBy('is_read', 'asc')
      ->orderBy('created_at', 'desc')
      ->get();

    if ($notifikasi->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data notifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $formattedData = $notifikasi->map(function ($item) {
        // $item->users->makeHidden('password');
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

  public function getalldiklat()
  {
    try {
        $diklat = Diklat::where('kategori_diklat_id', 1)->where('status_diklat_id', 4)->where('tgl_mulai', '>', Carbon::now()->format('Y-m-d'))->with('image')->get();
        $diklat->map(function($item){
            $item->path = env('URL_STORAGE') . $item->image->path;
            $item->ext = StorageFileHelper::getExtensionFromMimeType($item->ext);
            unset($item->image);
        });
        if($diklat->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'List diklat tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        return response()->json(new DataResource(Response::HTTP_OK, 'List diklat berhasil didapatkan', $diklat), Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function exportslip()
  {
    $user = User::where('id', Auth::user()->id)->first();
    $data = DataKaryawan::where('user_id', $user->id)->with('unitkerja', 'kelompok_gaji', 'ptkp', 'jabatan', 'statusKaryawan')->first();
    $datagaji = Penggajian::where('data_karyawan_id', $data->id)->with('detail_gajis')->first();
    $pdf = Pdf::loadView('slipgaji', ['data' => $data, 'user' => $user, 'gaji' => $datagaji])->setPaper('a4', 'portrait');
    // return $pdf->stream('contoh-pdf.pdf');

    $response = new WithoutDataResource(Response::HTTP_OK, 'Berhasil download slip gaji');
    return response()->json($response, Response::HTTP_OK)->withHeaders([
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="slipgaji.pdf"'
    ])->setContent($pdf->stream('slipgaji.pdf'));
  }

  public function readnotifiaksi(Request $request)
  {
    try {
        // $notifikasi = json_decode($request->notifikasi_id);
        // if (is_string($request->notifikasi_id)) {
        //     $notifikasi_id = explode(',', $request->notifikasi_id); // Ubah string menjadi array
        // }
        $notifikasi = Notifikasi::whereIn('id', json_decode($request->notifikasi_id))->where('user_id', Auth::user()->id)->update(['is_read' => 1]);
        // $notifikasi = Notifikasi::whereIn('id', json_decode($request->notifikasi_id))->whereJsonContains('user_id', Auth::user()->id)->update(['is_read' => 1]);
        // $notifikasi->is_read = 1;
        // $notifikasi->save();
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Notifkasi berhasil dibaca'), Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }


  public function getallpendidikan()
  {
    $kategori_pendidikan = KategoriPendidikan::all();
    if ($kategori_pendidikan->isEmpty()) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data pendidikan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'Retrieving all pendidikan for dropdown',
      'data' => $kategori_pendidikan
    ], Response::HTTP_OK);
  }

  public function destroyreadnotif()
  {
    try {
        $notif = Notifikasi::where('user_id', Auth::user()->id)->where('is_read', 1)->get();

        if($notif->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Notifikasi tidak ditemukan'), Response::HTTP_BAD_REQUEST);
        }

        $notif->delete();

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Notifkasi berhasil dihapus'), Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }


  public function countnotif()
  {
    $notif = Notifikasi::where('user_id', Auth::user()->id)->where('is_read', 0)->count();
    $mappingData = [
        'inbox' => $notif,
    ];
    return response()->json(new DataResource(Response::HTTP_OK, 'Notifkasi berhasil dihapus', $mappingData), Response::HTTP_OK);
  }
}
