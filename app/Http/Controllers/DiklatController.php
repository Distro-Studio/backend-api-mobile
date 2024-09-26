<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\Diklat;
use App\Models\Notifikasi;
use App\Models\PesertaDiklat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class DiklatController extends Controller
{
    public function getdetail($diklat)
    {
        try {
            $data = Diklat::where('id', $diklat)->with('image')->first();
            if(!$data) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            $ext = StorageFileHelper::getExtensionFromMimeType($data->image->ext);

            $data->path = env('URL_STORAGE') . $data->image->path;
            $data->ext = $ext;
            unset($data->image);

            return response()->json(new DataResource(Response::HTTP_OK, 'Diklat berhasil didapatkan', $data), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wronng'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    public function joindiklat(Request $request)
    {
        try {
            $diklat = Diklat::where('id', $request->diklat_id)->first();
            if(!$diklat) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Diklat tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            if($diklat->kuota <= $diklat->total_peserta +1) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kuota diklat sudah penuh'), Response::HTTP_BAD_REQUEST);
            }

            $isAlreadyJoin = PesertaDiklat::where('diklat_id', $request->diklat_id)->where('peserta', Auth::user()->id)->first();
            if($isAlreadyJoin) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Anda sudah bergabung'), Response::HTTP_NOT_FOUND);
            }

            $credential = PesertaDiklat::create([
                'diklat_id' => $request->diklat_id,
                'peserta' => Auth::user()->id,
            ]);

            $diklat->total_peserta += 1;
            $diklat->save();
            return response()->json(new DataResource(Response::HTTP_OK, 'Berhasil bergabung', $credential), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function storediklat(Request $request)
    {
        // StorageFileHelper::uploadToServer($request, Str::random(8), 'bpjs_ketenagakerjaan');
        try {
            $uploadserti = StorageFileHelper::uploadToServer($request, Str::random(8), 'dokumen');

            $berkasserti = Berkas::create([
                'user_id' => Auth::user()->id,
                'file_id' => $uploadserti['id_file']['id'],
                'nama' => 'KTP',
                'kategori_berkas_id' => 1,
                'status_berkas_id' => 1,
                'path' => $uploadserti['path'],
                'tgl_upload' => date('Y-m-d'),
                'nama_file' => $uploadserti['nama_file'],
                'ext' => $uploadserti['ext'],
                'size' => $uploadserti['size'],
            ]);

            if(!$berkasserti) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Sertifikat gagal di upload'), Response::HTTP_BAD_REQUEST);
            }

            $startTimeInput = $request->jam_mulai; // Contoh: "08:00:00"
            $endTimeInput = $request->jam_selesai;     // Contoh: "12:30:00"

            // Membuat objek Carbon dari input waktu
            // $startTime = Carbon::createFromFormat('H:i:s', $startTimeInput);
            // $endTime = Carbon::createFromFormat('H:i:s', $endTimeInput);

            // $startDay = Carbon::createFromFormat('Y-m-d', $request->tgl_mulai);
            // $endDay = Carbon::createFromFormat('Y-m-d', $request->tgl_selesai);

            // $dayDiff = $endDay->diffInDays() + 1;

            // $durasi = $endTime->diffInSeconds($startTime);

            $startDateTime = Carbon::parse($request->tgl_mulai . ' ' . $request->jam_mulai);
            $endDateTime = Carbon::parse($request->tgl_mulai . ' ' . $request->jam_selesai);

            $diffinsecond = $startDateTime->diffInSeconds($endDateTime);

            $totalDays = Carbon::parse($request->tgl_mulai)->diffInDays(Carbon::parse($request->tgl_selesai)) + 1;


            $diklat = Diklat::create([
                'dokumen_eksternal' => $berkasserti->id,
                'nama' => $request->nama,
                'kategori_diklat_id' => 2,
                'status_diklat_id' => 1,
                'deskripsi' => $request->deskripsi,
                'kuota' => 1,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'durasi' => $diffinsecond * $totalDays,
                'lokasi' => $request->lokasi,
                'skp' => $request->skp
            ]);

            $peserta = PesertaDiklat::create([
                'diklat_id' => $diklat->id,
                'peserta' => Auth::user()->id,
            ]);

            $notifikasi = Notifikasi::create([
                'kategori_notifikasi_id' => 4,
                'user_id' => Auth::user()->id,
                'message' => 'Pengajuan diklat eksternal ' . Auth::user()->nama,
                'is_read' => 0,
                'is_verifikasi' => 1,
              ]);

            return response()->json(new DataResource(Response::HTTP_OK,'Diklat berhasil diajukan', $diklat), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getriwayat()
    {
        try {

            $peserta = PesertaDiklat::where('peserta', Auth::user()->id)->with('diklat')->get();
            if($peserta->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada riwayat diklat'), Response::HTTP_NOT_FOUND);
            }
            $peserta->map(function($item){
                $item->diklat->status;
                $item->diklat->kategori;
                unset($item->id);
                unset($item->diklat_id);
                unset($item->peserta);
                unset($item->created_at);
                unset($item->updated_at);
            });

            return response()->json(new DataResource(Response::HTTP_OK, 'Riwayat diklat berhasil didapatkan', $peserta), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
