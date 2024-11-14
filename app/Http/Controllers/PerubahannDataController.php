<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PerubahannDataController extends Controller
{
  public function getdatapersonal()
  {
    try {
      $personal = DataKaryawan::where('user_id', Auth::user()->id)->with('kompetensi', 'golonganDarah', 'pendidikanTerakhir')->first();

      if (!$personal) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data personal tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      // $foto = Berkas::where('id', Auth::user()->foto_profil)->first();
      // if (!$foto) {
      //     $foto = null;
      // }
      $data = [
        // 'foto_profil' => env('URL_STORAGE') . $foto->path,
        'nama' => Auth::user()->nama ?? null,
        'kompetensi' => $personal->kompetensi->nama_kompetensi ?? null,
        'tempat_lahir' => $personal->tempat_lahir ?? null,
        'tanggal_lahir' => $personal->tgl_lahir ?? null,
        'no_hp' => $personal->no_hp ?? null,
        'jenis_kelamin' => $personal->jenis_kelamin ?? null, //nanti digantni
        'nik_ktp' => $personal->nik_ktp ?? null,
        'no_kk' => $personal->no_kk ?? null,
        'agama' => $personal->kategoriagama ?? null,
        'tinggi_badan' => $personal->tinggi_badan ?? null,
        'alamat' => $personal->alamat ?? null,
        'no_telp' => $personal->no_hp ?? null,
        'golongan_darah' => $personal->golonganDarah ?? null,
        // 'tinggi_badan' => $personal->tinggi_badan,
        'berat_badan' => $personal->berat_badan ?? null,
        'no_ijasah' => $personal->no_ijazah ?? null,
        'tahun_lulus' => $personal->tahun_lulus ?? null,
        'pendidikan_terakhir' => $personal->pendidikanTerakhir ?? null,
        'gelar_depan' => $personal->gelar_depan ?? null,
      ];

      return response()->json(new DataResource(Response::HTTP_OK, 'Data personal ditemukan', $data), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
