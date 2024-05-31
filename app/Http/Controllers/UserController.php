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
        return response()->json(new DataResource(Response::HTTP_OK, 'User berhasil di dapatkan', Auth::user()));
    }
}
