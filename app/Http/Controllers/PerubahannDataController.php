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
            $personal = DataKaryawan::where('user_id', Auth::user()->id)->with('kompetensi')->first();

            if (!$personal) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data personal tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            $foto = Berkas::where('id', Auth::user()->foto_profil)->first();

            $data = [
                'foto_profil' => env('URL_STORAGE') . $foto->path,
                'nama' => Auth::user()->nama,
                'kompetensi' => $personal->kompetensi->nama_kompetensi,
                'tempat_lahir' => $personal->tempat_lahir,
                'tanggal_lahir' => $personal->tgl_lahir,
                'no_hp' => $personal->no_hp,
                'jenis_kelamin' => $personal->jenis_kelamin, //nanti digantni
                'nik_ktp' => $personal->nik_ktp,
                'no_kk' => $personal->no_kk,
                'agama' => $personal->kategori_agama_id,
                'tinggi_badan' => $personal->tinggi_badan,
                // 'alamat' =>
            ];
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
