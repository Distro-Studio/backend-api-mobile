<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function tokencheck($token)
    {
        $user = User::where('remember_token', $token)->first();
        if(!$user)
        {
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Token tidak valid'
            // ]);
            return false;
        }

        $data = DataKaryawan::where('user_id', $user->id)->first();

        $from = $user->updated_at;
        $to = date('Y-m-d H:i:s');

        $fromformat = Carbon::parse($from)->timezone('Asia/Jakarta');
        $toformat = Carbon::parse($to)->timezone('Asia/Jakarta');
        // $message = 30 - $fromformat->diffInMinutes($toformat) . ' Menit sebelum token expired';
        if($fromformat->diffInMinutes($toformat) > 30)
        {
            return response()->json([
                'status' => false,
                'message' => 'Token expired'
            ]);
            // return false;
        }

        // return true;

        return response()->json([
            'status' => true,
            'message' => 'Token valid',
            'data' => [
                'token' => $token,
                'email' => $data->email,
            ]
        ]);
    }

    public function checktoken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ], [
            'token.required' => 'Token harus diisi'
        ]);

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }


        $user = User::where('remember_token', $request->token)->first();
        if (!$user)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Token tidak valid'),Response::HTTP_NOT_FOUND);
        }

        $data = DataKaryawan::where('user_id', $user->id)->first();

        $from = $user->updated_at;
        $to = date('Y-m-d H:i:s');

        $fromformat = Carbon::parse($from)->timezone('Asia/Jakarta');
        $toformat = Carbon::parse($to)->timezone('Asia/Jakarta');
        // $message = 30 - $fromformat->diffInMinutes($toformat) . ' Menit sebelum token expired';
        if($fromformat->diffInMinutes($toformat) > 30)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Token telah expired'),Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new DataResource(Response::HTTP_OK, 'Token berhasil diverifikasi', [
            'token' => $request->token,
            'email' => $data->email,
        ]), Response::HTTP_OK);
    }

    public function changepass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|confirmed|min:8'
        ], [
            'token.required' => 'Token harus diisi',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Password tidak sesuai',
            'password.min' => 'Password harus minimal 8 karakter.',
            // 'password.regex' => 'Password harus mengandung huruf kecil, huruf besar, angka, dan simbol.'
        ]);

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
        }

        // dd(Auth::user());
        // $user = User::where('remember_token', $request->token)->first();
        $user = User::where('id', Auth::user()->id)->first();
        // if (!$user)
        // {
        //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Token tidak valid'),Response::HTTP_NOT_FOUND);
        // }

        $data = DataKaryawan::where('user_id', $user->id)->first();

        $from = $user->updated_at;
        $user->data_completion_step = 0;
        $to = date('Y-m-d H:i:s');

        $fromformat = Carbon::parse($from)->timezone('Asia/Jakarta');
        $toformat = Carbon::parse($to)->timezone('Asia/Jakarta');
        // $message = 30 - $fromformat->diffInMinutes($toformat) . ' Menit sebelum token expired';
        if($fromformat->diffInMinutes($toformat) > 30)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Token telah expired'),Response::HTTP_NOT_ACCEPTABLE);
        }


        try{
            $user->password = Hash::make($request->password);
            $user->remember_token = null;
            if($user->save()){
                return response()->json(new DataResource(Response::HTTP_OK, 'Berhasil menyimpan password baru', $user), Response::HTTP_OK);
            } else{
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyimpan password baru'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch(Exception $e){
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyimpan password baru'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


}
