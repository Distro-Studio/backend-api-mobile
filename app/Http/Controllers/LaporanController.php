<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\Notifikasi;
use App\Models\Pelaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function storelaporan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelaku' => 'required|exists:users,id',
            'tgl_kejadian' => 'required',
            'lokasi' => 'required',
            'kronologi' => 'required',
            'foto' => 'required',
        ], [
            'pelaku.required' => 'Pelaku harus dipilih',
            'pelaku.exists' => 'Pelaku tidak ditemukan',
            'tgl_kejadian.required' => 'Tgl kejadian harus diisi',
            'lokasi.required' => 'Lokasi harus diisi',
            'kronologi.required' => 'Kronologi harus diisi',
            'foto.required' => 'Foto harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            $dataupload = StorageFileHelper::uploadToServer($request, Str::random(8), 'foto');
            $saveberkas = Berkas::create([
                'user_id' => Auth::user()->id,
                'file_id' => $dataupload['id_file']['id'],
                'nama' => 'Laporan '. Auth::user()->name,
                'kategori_berkas_id' => 1, //pribadi
                'path' => $dataupload['path'],
                'tgl_upload' => date('Y-m-d'),
                'nama_file' => $dataupload['nama_file'],
                'ext' => $dataupload['ext'],
                'size' => $dataupload['size'],
            ]);

            $laporan = Pelaporan::create([
                'pelapor' => Auth::user()->id,
                'pelaku' => $request->pelaku,
                'tgl_kejadian' => $request->tgl_kejadian,
                'lokasi' => $request->lokasi,
                'kronologi' => $request->kronologi,
                'upload_foto' => $saveberkas->id
            ]);

            $notifikasi = Notifikasi::create([
                'kategori_notifikasi_id' => 8, //laporan
                'user_id' => Auth::user()->id,
                'message' => 'Pelaporan berhasil diajukan',
                'is_read' => 0
            ]);

            Notifikasi::create([
                'kategori_notifikasi_id' => 9, //laporan
                'user_id' => 1,
                'message' => 'Pelaporan baru '. Auth::user()->name,
                'is_read' => 0
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Laporan berhasil disimpan', $notifikasi), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wronng'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
