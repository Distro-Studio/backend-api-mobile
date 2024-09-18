<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\TukarJadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TukarJadwalController extends Controller
{
    public function getpengajuan(Request $request)
    {
        try {
            $query = TukarJadwal::query();
            $query->where('user_pengajuan', Auth::user()->id);

            $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth();
            $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth();
            $offset = 4;

            if ($request->filled('tgl_mulai')){
                $tgl_mulai = Carbon::parse($request->tgl_mulai);
            }

            if ($request->filled('tgl_selesai')){
                $tgl_selesai = Carbon::parse($request->tgl_selesai);
            }

            $query->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

            if ($request->filled('jenis')){
                $query->where('kategori_penukaran_id', $request->jenis);
            }

            if ($request->filled('status')){
                $query->where('status_penukaran_id', $request->status);
            }

            $query->with('userPengajuan', 'userDitukar', 'statusPengajuan', 'kategoriPengajuan');

            if ($request->filled('offset')){
                $offset = $request->offset;
            }

            $tukar = $query->paginate($offset);

            return response()->json(new DataResource(Response::HTTP_OK, 'List pengajuan tukar jadwal berhasil didapatkan', $tukar), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getpermintaan(Request $request)
    {
        try {
            $query = TukarJadwal::query();
            $query->where('user_ditukar', Auth::user()->id);

            $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth();
            $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth();
            $offset = 4;

            if ($request->filled('tgl_mulai')){
                $tgl_mulai = Carbon::parse($request->tgl_mulai);
            }

            if ($request->filled('tgl_selesai')){
                $tgl_selesai = Carbon::parse($request->tgl_selesai);
            }

            $query->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

            if ($request->filled('jenis')){
                $query->where('kategori_penukaran_id', $request->jenis);
            }

            if ($request->filled('status')){
                $query->where('status_penukaran_id', $request->status);
            }

            $query->with('userPengajuan', 'userDitukar', 'statusPengajuan', 'kategoriPengajuan');

            if ($request->filled('offset')){
                $offset = $request->offset;
            }

            $tukar = $query->paginate($offset);

            return response()->json(new DataResource(Response::HTTP_OK, 'List pengajuan tukar jadwal berhasil didapatkan', $tukar), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getswap(Request $request)
    {
        try {
            $query = TukarJadwal::query();
            $query->where('user_ditukar', Auth::user()->id)->orWhere('user_pengajuan', Auth::user()->id);

            $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth();
            $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth();
            $offset = 4;

            if ($request->filled('tgl_mulai')){
                $tgl_mulai = Carbon::parse($request->tgl_mulai);
            }

            if ($request->filled('tgl_selesai')){
                $tgl_selesai = Carbon::parse($request->tgl_selesai);
            }

            $query->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

            if ($request->filled('jenis')){
                $query->where('kategori_penukaran_id', $request->jenis);
            }

            if ($request->filled('status')){
                $query->where('status_penukaran_id', $request->status);
            }

            $query->with('userPengajuan', 'userDitukar', 'statusPengajuan', 'kategoriPengajuan');

            if ($request->filled('offset')){
                $offset = $request->offset;
            }

            $tukar = $query->paginate($offset);

            return response()->json(new DataResource(Response::HTTP_OK, 'List pengajuan tukar jadwal berhasil didapatkan', $tukar), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
