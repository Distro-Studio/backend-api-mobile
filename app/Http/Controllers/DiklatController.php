<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Diklat;
use App\Models\PesertaDiklat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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

            $isAlreadyJoin = PesertaDiklat::where('diklat_id', $request->diklat_id)->where('peserta', Auth::user()->id)->first();
            if($isAlreadyJoin) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Anda sudah bergabung'), Response::HTTP_NOT_FOUND);
            }

            $credential = PesertaDiklat::create([
                'diklat_id' => $request->diklat_id,
                'peserta' => Auth::user()->id,
            ]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Berhasil bergabung', $credential), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
