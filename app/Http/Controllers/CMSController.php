<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\AboutHospital;
use App\Models\MateriPelatihan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CMSController extends Controller
{
    public function getabout()
    {
        $data = AboutHospital::where('id', 1)->with('berkas_1', 'berkas_2', 'berkas_3', 'user')->get();
        if($data->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $mappingData = $data->map(function($d) {
            return [
                'id' => $d->id,
                'konten' => $d->konten,
                'edited_by' => $d->user ? [
                    'id' => $d->user->id,
                    'nama' => $d->user->nama,
                    'username' => $d->user->username,
                    'data_karyawan_id' => $d->user->data_karyawan_id,
                    'status_aktif' => $d->user->status_aktif,
                ] : null,
                'gambar_about_1' => $d->berkas_1 ? [
                    'id' => $d->berkas_1->id,
                    'nama' => $d->berkas_1->nama,
                    'nama_file' => $d->berkas_1->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_1->path,
                    'ext' => $d->berkas_1->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_1->ext)
                    'size' => $d->berkas_1->size,
                ] : null,
                'gambar_about_2' => $d->berkas_2 ? [
                    'id' => $d->berkas_2->id,
                    'nama' => $d->berkas_2->nama,
                    'nama_file' => $d->berkas_2->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_2->path,
                    'ext' => $d->berkas_2->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_2->ext)
                    'size' => $d->berkas_2->size,
                ] : null,
                'gambar_about_3' => $d->berkas_3 ? [
                    'id' => $d->berkas_3->id,
                    'nama' => $d->berkas_3->nama,
                    'nama_file' => $d->berkas_3->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_3->path,
                    'ext' => $d->berkas_3->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_3->ext)
                    'size' => $d->berkas_3->size,
                ] : null,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at
            ];
        });

        return response()->json(new DataResource(Response::HTTP_OK, 'Tentang rumah sakit berhasil didapatkan', $mappingData), Response::HTTP_OK);
    }

    public function getvisimisi()
    {
        $data = AboutHospital::where('id', 2)->with('berkas_1', 'berkas_2', 'berkas_3', 'user')->get();
        if($data->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $mappingData = $data->map(function($d) {
            return [
                'id' => $d->id,
                'konten' => $d->konten,
                'edited_by' => $d->user ? [
                    'id' => $d->user->id,
                    'nama' => $d->user->nama,
                    'username' => $d->user->username,
                    'data_karyawan_id' => $d->user->data_karyawan_id,
                    'status_aktif' => $d->user->status_aktif,
                ] : null,
                'gambar_about_1' => $d->berkas_1 ? [
                    'id' => $d->berkas_1->id,
                    'nama' => $d->berkas_1->nama,
                    'nama_file' => $d->berkas_1->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_1->path,
                    'ext' => $d->berkas_1->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_1->ext)
                    'size' => $d->berkas_1->size,
                ] : null,
                'gambar_about_2' => $d->berkas_2 ? [
                    'id' => $d->berkas_2->id,
                    'nama' => $d->berkas_2->nama,
                    'nama_file' => $d->berkas_2->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_2->path,
                    'ext' => $d->berkas_2->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_2->ext)
                    'size' => $d->berkas_2->size,
                ] : null,
                'gambar_about_3' => $d->berkas_3 ? [
                    'id' => $d->berkas_3->id,
                    'nama' => $d->berkas_3->nama,
                    'nama_file' => $d->berkas_3->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_3->path,
                    'ext' => $d->berkas_3->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_3->ext)
                    'size' => $d->berkas_3->size,
                ] : null,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at
            ];
        });

        return response()->json(new DataResource(Response::HTTP_OK, 'Visi misi rumah sakit berhasil didapatkan', $mappingData), Response::HTTP_OK);
    }

    public function getmutu()
    {
        $data = AboutHospital::where('id', 3)->with('berkas_1', 'berkas_2', 'berkas_3', 'user')->get();
        if($data->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $mappingData = $data->map(function($d) {
            return [
                'id' => $d->id,
                'konten' => $d->konten,
                'edited_by' => $d->user ? [
                    'id' => $d->user->id,
                    'nama' => $d->user->nama,
                    'username' => $d->user->username,
                    'data_karyawan_id' => $d->user->data_karyawan_id,
                    'status_aktif' => $d->user->status_aktif,
                ] : null,
                'gambar_about_1' => $d->berkas_1 ? [
                    'id' => $d->berkas_1->id,
                    'nama' => $d->berkas_1->nama,
                    'nama_file' => $d->berkas_1->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_1->path,
                    'ext' => $d->berkas_1->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_1->ext)
                    'size' => $d->berkas_1->size,
                ] : null,
                'gambar_about_2' => $d->berkas_2 ? [
                    'id' => $d->berkas_2->id,
                    'nama' => $d->berkas_2->nama,
                    'nama_file' => $d->berkas_2->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_2->path,
                    'ext' => $d->berkas_2->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_2->ext)
                    'size' => $d->berkas_2->size,
                ] : null,
                'gambar_about_3' => $d->berkas_3 ? [
                    'id' => $d->berkas_3->id,
                    'nama' => $d->berkas_3->nama,
                    'nama_file' => $d->berkas_3->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_3->path,
                    'ext' => $d->berkas_3->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_3->ext)
                    'size' => $d->berkas_3->size,
                ] : null,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at
            ];
        });

        return response()->json(new DataResource(Response::HTTP_OK, 'Tentang rumah sakit berhasil didapatkan', $mappingData), Response::HTTP_OK);
    }

    public function getmateri()
    {
        $data = MateriPelatihan::where('id', 2)->with('user', 'berkas_1', 'berkas_2', 'berkas_3')->get();
        if($data->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tidak ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $mappingData = $data->map(function($d){
            return [
                'id' => $d->id,
                'judul' => $d->judul,
                'deskripsi' => $d->deskripsi,
                'user' => $d->user ? [
                    'id' => $d->user->id,
                    'nama' => $d->user->nama,
                    'username' => $d->user->username,
                    'data_karyawan_id' => $d->user->data_karyawan_id,
                    'status_aktif' => $d->user->status_aktif,
                ] : null,
                'dokumen_materi_1' => $d->berkas_1 ? [
                    'id' => $d->berkas_1->id,
                    'nama' => $d->berkas_1->nama,
                    'nama_file' => $d->berkas_1->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_1->path,
                    'ext' => $d->berkas_1->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_1->ext)
                    'size' => $d->berkas_1->size,
                ] : null,
                'dokumen_materi_2' => $d->berkas_2 ? [
                    'id' => $d->berkas_2->id,
                    'nama' => $d->berkas_2->nama,
                    'nama_file' => $d->berkas_2->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_2->path,
                    'ext' => $d->berkas_2->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_2->ext)
                    'size' => $d->berkas_2->size,
                ] : null,
                'dokumen_materi_3' => $d->berkas_3 ? [
                    'id' => $d->berkas_3->id,
                    'nama' => $d->berkas_3->nama,
                    'nama_file' => $d->berkas_3->nama_file,
                    'path' => env('URL_STORAGE') . $d->berkas_3->path,
                    'ext' => $d->berkas_3->ext,
                    // 'ext' => StorageFileHelper::getExtensionFromMimeType($d->berkas_3->ext)
                    'size' => $d->berkas_3->size,
                ] : null,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at
            ];
        });

        return response()->json(new DataResource(Response::HTTP_OK, 'Materi pelatihan berhasil didapatkan', $mappingData), Response::HTTP_OK);
    }
}
