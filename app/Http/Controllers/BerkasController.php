<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Berkas;
use App\Models\Notifikasi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WithoutDataResource;
use Symfony\Component\HttpFoundation\Response;

class BerkasController extends Controller
{
    public function storeberkas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'label' => 'required',
        ]);

        $userLoggedin = Auth::user()->id;

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            $dataupload = StorageFileHelper::uploadToServer($request, Str::random(8), 'file');

            $saveberkas = Berkas::create([
                'user_id' => $userLoggedin,
                'file_id' => $dataupload['id_file']['id'],
                'nama' => $request->label,
                'kategori_berkas_id' => 1, //pribadi
                'status_berkas_id' => 1,
                'path' => $dataupload['path'],
                'tgl_upload' => date('Y-m-d'),
                'nama_file' => $dataupload['nama_file'],
                'ext' => $dataupload['ext'],
                'size' => $dataupload['size'],
            ]);

            $this->createNotifikasiBerkas($userLoggedin);

            return response()->json(new DataResource(Response::HTTP_OK, 'Berkas berhasil di upload', $saveberkas), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getallberkas()
    {
        try {
            $berkas = Berkas::where('user_id', Auth::user()->id)->where('kategori_berkas_id', 1)->where('status_berkas_id', 2)->with('kategori_berkas', 'status_berkas', 'verifikator')->latest()->get();

            if ($berkas->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Berkas tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            $data = $berkas->map(function ($i) {
                $ext = StorageFileHelper::getExtensionFromMimeType($i->ext);
                return [
                    'id' => $i->id,
                    // 'file_id' => $i->file_id,
                    'label' => $i->nama,
                    'tgl_upload' => $i->tgl_upload,
                    'ext' => $ext,
                    'size' => $i->size,
                    'path' => env('URL_STORAGE') . $i->path,
                    'kategori_berkas' => $i->kategori_berkas,
                    'status_berkas' => $i->status_berkas,
                    'verifikator' => $i->verifikator,
                    'alasan' => $i->alasan

                ];
            });

            return response()->json(new DataResource(Response::HTTP_OK, 'Berkas berhasil didapatkan', $data), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function renameberkas(Request $request)
    {
        try {
            $query = Berkas::query();
            $query->where('id', $request->file_id);

            $cekberkas = $query->exists();

            if ($cekberkas) {
                $datalama = $query->first();

                $data = [
                    'nama_awal' => $datalama->nama,
                    'nama_dirubah' => $request->nama,
                ];

                $berkas = $query->update([
                    'nama' => $request->nama,
                ]);

                return response()->json(new DataResource(Response::HTTP_OK, 'Nama berkas berhasil di ubah', $data), Response::HTTP_OK);
            } else {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Berkas tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadberkas(Request $request)
    {
        try {
            $berkas = Berkas::query();
            $berkas->where('id', $request->berkas_id)->where('user_id', Auth::user()->id);
            $cek = $berkas->exists();

            if (!$cek) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Berkas tidak ditemukan'), Response::HTTP_NOT_FOUND);
            }

            $berkasfile = $berkas->first();
            $fileContent = StorageFileHelper::downloadFromServer($berkasfile);
            // dd($fileContent);
            return response($fileContent['data'], 200)
                ->header('Content-Type', $berkasfile->ext)
                ->header('Content-Disposition', 'attachment; filename="' . $fileContent['filename'] . '"');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'berkas_id' => 'required|integer',
        ], [
            'berkas_id.required' => 'ID berkas harus di isi',
            'berkas_id.integer' => 'ID berkas harus berupa ID angka',
        ]);

        if ($validation->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, $validation->errors()), Response::HTTP_NOT_FOUND);
        }

        try {
            $berkas = Berkas::find($request->berkas_id);
            $name = $berkas->nama;
            if (!$berkas) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Berkas tidak ditemuka'), Response::HTTP_NOT_FOUND);
            }

            // if($berkas->user_id) {

            // }
            $delete = StorageFileHelper::deleteFromServer($berkas);
            $berkas->delete();
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berkas ' . $name . ' berhasil di hapus'), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createNotifikasiBerkas($userId)
    {
        $konversiTgl = Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM YYYY');
        $dataAkun = User::find($userId);

        Notifikasi::create([
            'kategori_notifikasi_id' => 6,
            'user_id' => 1,
            'message' => "Notifikasi untuk Super Admin: Pengajuan berkas dari karyawan '{$dataAkun->nama}' pada tanggal '{$konversiTgl}' menunggu untuk diverifikasi.",
            'is_read' => false,
            'is_verifikasi' => true,
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
    }
}
