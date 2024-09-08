<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\RiwayatIzin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class IzinController extends Controller
{
    public function getriwayat(Request $request)
    {
        try {
            $query = RiwayatIzin::query();
            $query->where('user_id', Auth::id());

            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $endDate = Carbon::now()->endOfYear()->format('Y-m-d');

            if($request->filled('tgl_mulai')) {
                $startDate = Carbon::parse($request->tgl_mulai);
            }

            if($request->filled('tgl_selesai')) {
                $endDate = Carbon::parse($request->tgl_selesai);
            }

            $query->whereBetween('tgl_izin', [$startDate, $endDate]);

            if($request->filled('status')){
                $query->where('status_izin_id', $request->status);
            }

            $data = $query->get();

            if($data->isEmpty()){
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Izin tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            return response()->json(new DataResource(Response::HTTP_OK, 'Izin berhasil didapatkan', $data), Response::HTTP_OK);


        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Somethinng wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'tgl_izin' => 'required|date',
            'waktu_izin' => 'required',
            'durasi' => 'required|max:7200',
            'keterangan' => 'required',
        ], [
            'tgl_izin.required' => 'Tanggal Izin Wajib Diisi',
            'tgl_izin.date' => 'Tanggal Izin Harus Berupa Tanggal',
            'waktu_izin.required' => 'Waktu Izin Wajib Diisi',
            'durasi.required' => 'Durasi Izin Wajib Diisi',
            'durasi.max' => 'Durasi tidak boleh lebih dari 2 jam',
            'keterangan.required' => 'Keterangan Izin Wajib Diisi',
        ]);

        if($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $validator->errors()), Response::HTTP_BAD_REQUEST);
        }

        try {
            $cekkuotabulan = RiwayatIzin::where('user_id', Auth::user()->id)->whereMonth('tgl_izin', Carbon::parse($request->tgl_izin)->month)->get();

            if ($cekkuotabulan->isNotEmpty()){
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kuota bulan ini sudah habis'), Response::HTTP_BAD_REQUEST);
            }

            $izin = RiwayatIzin::create([
                'user_id' => Auth::user()->id,
                'tgl_izin' => $request->tgl_izin,
                'waktu_izin' => $request->waktu_izin,
                'durasi' => $request->durasi,
                'keterangan' => $request->keterangan,
                'status_izin_id' => 1,
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Izin berhasil diajukan', $izin), Response::HTTP_OK);


        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
