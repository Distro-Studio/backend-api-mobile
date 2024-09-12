<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
  public function checkuser()
  {
    // return response()->json(new DataResource(Response::HTTP_OK, 'User berhasil di dapatkan', Auth::user()));
    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => 'User berhasil di dapatkan',
      'data' =>
        [
          'user' => Auth::user(),
          'unit_kerja' => Auth::user()->dataKaryawan->unitkerja
        ]
    ], Response::HTTP_OK);
  }
}
