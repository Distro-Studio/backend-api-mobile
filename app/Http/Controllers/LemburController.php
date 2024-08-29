<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LemburController extends Controller
{
    public function getstatistik()
    {
        try {
            $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
            $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth()->toDateString();
            $query = Lembur::query();
            $query->where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

            $totallembur = $query->count();
            $totalwaktu = $query->sum('durasi');
            // $totallembur = Lembur::where('user_id', Auth::user()->id)->where('status_lembur_id', 3)->whereBetween('tgl_pengajuan', [$tgl_mulai, $tgl_selesai])->count();

            $data = [
                'total_lembur' => $totallembur,
                'total_waktu' => $totalwaktu
            ];

            return response()->json(new DataResource(Response::HTTP_OK, 'Statistik lembur berhasil didapatkan', $data), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $query = Lembur::query();
            // $query->where('user_id', Auth::user()->id)->where('status_lembur_id', 3)->whereBetween('tgl_pengajuan', [$tgl_mulai, $tgl_selesai]);
            $query->where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

            $data = $query->with('user')->paginate($offset);

            return response()->json(new DataResource(Response::HTTP_OK, 'Riwayat lembur berhasil didapatkan', $data), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
