<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Notifikasi;
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

            $tahunnow = Carbon::now()->format('Y');
            // $endDate = Carbon::now()->endOfYear()->format('Y-m-d');

            if($request->filled('tahun')) {
                $tahunnow = $request->tahun;
            }

            // if($request->filled('tgl_selesai')) {
            //     $endDate = Carbon::parse($request->tgl_selesai);
            // }

            $query->whereYear('tgl_izin', $tahunnow);

            if($request->filled('status')){
                $query->where('status_izin_id', $request->status);
            }
            $query->with('statusizin');

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
            'durasi' => 'required|max:7201',
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

        // if()

        try {
            $cekkuotabulan = RiwayatIzin::where('user_id', Auth::user()->id)->where('status_izin_id', 2)->whereMonth('tgl_izin', Carbon::parse($request->tgl_izin)->month)->get();

            if ($cekkuotabulan->isNotEmpty()){
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kuota bulan ini sudah habis'), Response::HTTP_BAD_REQUEST);
            }

            if($request->durasi >7200) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Durasi tidak boleh lebih dari 2 jam'), Response::HTTP_BAD_REQUEST);
            }

            $izin = RiwayatIzin::create([
                'user_id' => Auth::user()->id,
                'tgl_izin' => $request->tgl_izin,
                'waktu_izin' => $request->waktu_izin,
                'durasi' => $request->durasi,
                'keterangan' => $request->keterangan,
                'status_izin_id' => 1,
            ]);

            $notifikasi = Notifikasi::create([
                'kategori_notifikasi_id' => 10,
                'user_id' => Auth::user()->id,
                'message' => 'Pengajuan Cuti ' . Auth::user()->nama,
                'is_read' => 0,
                'is_verifikasi' => 1,
              ]);

            Notifikasi::create([
                'kategori_notifikasi_id' => 10,
                'user_id' => 1,
                'message' => 'Pengajuan Cuti ' . Auth::user()->nama,
                'is_read' => 0,
                'is_verifikasi' => 1,
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Izin berhasil diajukan', $izin), Response::HTTP_OK);


        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
