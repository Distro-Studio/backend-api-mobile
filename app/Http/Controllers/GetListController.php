<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
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

    public function getkaryawanunitkerja(){
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
                ->with(['user', 'kompetensi', 'jadwal' => function($query) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $query->whereBetween('tgl_mulai', [$startDate, $endDate]);
                    }
                }]);
            $user = $query->get();

            return response()->json(new DataResource(Response::HTTP_OK, 'User berhasil didapatkan', $user), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}