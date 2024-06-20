<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function passreset(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak sesuai',
        ]);

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
        }

        $checkemail = DataKaryawan::where('email', $request->email)->first();

        if(!$checkemail)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND,'Email anda tidak ditemukan'),Response::HTTP_NOT_FOUND);
        }


        $user = User::where('id', $checkemail->user_id)->first();
        $user->remember_token = Str::random(60);
        $user->updated_at = date('Y-m-d H:i:s');
        $user->save();

        //disini script untuk kirim email nya

        return response()->json(new DataResource(Response::HTTP_OK, 'Link reset password berhasil dikirim', ['email' => $request->email]), Response::HTTP_OK);
    }
}
