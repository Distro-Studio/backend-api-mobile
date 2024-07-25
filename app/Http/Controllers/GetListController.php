<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetListController extends Controller
{
    public function getlistagama()
    {
        try {
            $agama = KategoriDarah::select('id', 'label')->get();
            return response()->json(new DataResource(Response::HTTP_OK, 'List agama berhasil didapatkan', $agama), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getlistgoldar()
    {
        try {
            $goldar = KategoriDarah::select('id', 'label')->get();
            return response()->json(new DataResource(Response::HTTP_OK, 'List golongan darah berhasil didapatkan', $goldar), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
