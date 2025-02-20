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

            if($diklat->kuota < $diklat->total_peserta +1) {
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
            $berkasid = null;
            if($request->dokumen != null) {
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

                $berkasid = $berkasserti->id;
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
                'dokumen_eksternal' => $berkasid,
                'nama' => $request->nama,
                'kategori_diklat_id' => 2,
                'status_diklat_id' => 1,
                'deskripsi' => $request->deskripsi,
                'kuota' => 1,
                'total_peserta' => 1,
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
                'message' => 'Pengajuan diklat eksternal ' . Auth::user()->nama . ' berhasil terkirim',
                'is_read' => 0,
                'is_verifikasi' => 1,
              ]);

            Notifikasi::create([
                'kategori_notifikasi_id' => 4,
                'user_id' => 1,
                'message' => 'Pengajuan diklat eksternal ' . Auth::user()->nama . ' berhasil terkirim',
            ]);

            return response()->json(new DataResource(Response::HTTP_OK,'Diklat berhasil diajukan', $diklat), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getriwayat()
    {
        try {

            $peserta = PesertaDiklat::where('peserta', Auth::user()->id)->with('diklat', 'diklat.image', 'diklat.kategori', 'diklat.status', 'diklat.dokumen')->get();
            if($peserta->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada riwayat diklat'), Response::HTTP_NOT_FOUND);
            }
            // $peserta->map(function($item){
            //     $item->diklat->status;
            //     $item->diklat->kategori;
            //     unset($item->id);
            //     unset($item->diklat_id);
            //     unset($item->peserta);
            //     unset($item->created_at);
            //     unset($item->updated_at);
            // });

            $peserta = $peserta->map(function($item){
                unset($item->id);
                unset($item->diklat_id);
                unset($item->peserta);
                unset($item->created_at);
                unset($item->updated_at);
                return [
                    'diklat' => [
                        "id" => $item->diklat->id,
                        "gambar" => $item->diklat->image ? [
                            "id" => $item->diklat->image->id ?? null,
                            "user_id" => $item->diklat->image->user_id ?? null,
                            "file_id" => $item->diklat->image->file_id ?? null,
                            "nama" => $item->diklat->image->nama ?? null,
                            "kategori_berkas_id" => $item->diklat->image->kategori_berkas_id ?? null,
                            "status_berkas_id" => $item->diklat->image->status_berkas_id ?? null,
                            "path" => env('URL_STORAGE').($item->diklat->image->path ?? ''),
                            "tgl_upload" => $item->diklat->image->tgl_upload ?? null,
                            "nama_file" => $item->diklat->image->nama_file ?? null,
                            "ext" => StorageFileHelper::getExtensionFromMimeType($item->diklat->image->ext) ?? null,
                            "size" => $item->diklat->image->size ?? null,
                            "verifikator_1" => $item->diklat->image->verifikator_1 ?? null,
                            "alasan" => $item->diklat->image->alasan ?? null,
                        ] : null,
                        "dokumen_eksternal" => $item->diklat->dokumen ? [
                            "id" => $item->diklat->dokumen->id ?? null,
                            "user_id" => $item->diklat->dokumen->user_id ?? null,
                            "file_id" => $item->diklat->dokumen->file_id ?? null,
                            "nama" => $item->diklat->dokumen->nama ?? null,
                            "kategori_berkas_id" => $item->diklat->dokumen->kategori_berkas_id ?? null,
                            "status_berkas_id" => $item->diklat->dokumen->status_berkas_id ?? null,
                            "path" => env('URL_STORAGE').($item->diklat->dokumen->path ?? ''),
                            "tgl_upload" => $item->diklat->dokumen->tgl_upload ?? null,
                            "nama_file" => $item->diklat->dokumen->nama_file ?? null,
                            "ext" => StorageFileHelper::getExtensionFromMimeType($item->diklat->dokumen->ext) ?? null,
                            "size" => $item->diklat->dokumen->size ?? null,
                            "verifikator_1" => $item->diklat->dokumen->verifikator_1 ?? null,
                            "alasan" => $item->diklat->dokumen->alasan ?? null,
                        ] : null,
                        "nama" => $item->diklat->nama ?? null,
                        "kategori" => [
                            'id' => $item->diklat->kategori->id ?? null,
                            'label' => $item->diklat->kategori->label ?? null
                        ],
                        "status" => [
                            'id' => $item->diklat->status->id ?? null,
                            'label' => $item->diklat->status->label ?? null
                        ],
                        "certificate_published" => $item->diklat->certificate_published ?? null,
                        "certificate_verified_by" => $item->diklat->certificate_verified_by ?? null,
                        "deskripsi" => $item->diklat->deskripsi ?? null,
                        "kuota" => $item->diklat->kuota ?? null,
                        "total_peserta" => $item->diklat->total_peserta ?? null,
                        "skp" => $item->diklat->skp ?? null,
                        "tgl_mulai" => $item->diklat->tgl_mulai ?? null,
                        "tgl_selesai" => $item->diklat->tgl_selesai ?? null,
                        "jam_mulai" => $item->diklat->jam_mulai ?? null,
                        "jam_selesai" => $item->diklat->jam_selesai ?? null,
                        "durasi" => $item->diklat->durasi ?? null,
                        "lokasi" => $item->diklat->lokasi ?? null,
                        "verifikator_1" => $item->diklat->verifikator_1 ?? null,
                        "verifikator_2" => $item->diklat->verifikator_2 ?? null,
                        "alasan" => $item->diklat->alasan ?? null,
                    ]
                ];
            });

            return response()->json(new DataResource(Response::HTTP_OK, 'Riwayat diklat berhasil didapatkan', $peserta), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
