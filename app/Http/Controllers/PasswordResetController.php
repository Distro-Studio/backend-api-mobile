<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
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
    public function passreset(UpdateUserPasswordRequest $request)
    {
        // TODO: Buat validasi email dahulu

        $user = Auth::user();

        $dataKaryawan = $user->dataKaryawan;
        if ($dataKaryawan && $dataKaryawan->email == 'super_admin@admin.rski') {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memperbarui kata sandi pada role Super Admin.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        if (isset($data['password'])) {
            // Check if the current password is correct
            $currentPassword = $request->input('current_password');
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kata sandi yang anda masukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            // TODO: Verify email before changing password

            // Update the new password
            $data['password'] = Hash::make($data['password']);
        }
        /** @var \App\Models\User $user **/
        $user->fill($data)->save();
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berhasil memperbarui kata sandi anda.', $user), Response::HTTP_OK);
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

        // $from = $user->updated_at;
        $user->data_completion_step = 0;
        // $to = date('Y-m-d H:i:s');

        // $fromformat = Carbon::parse($from)->timezone('Asia/Jakarta');
        // $toformat = Carbon::parse($to)->timezone('Asia/Jakarta');
        // // $message = 30 - $fromformat->diffInMinutes($toformat) . ' Menit sebelum token expired';
        // if($fromformat->diffInMinutes($toformat) > 30)
        // {
        //     return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Token telah expired'),Response::HTTP_NOT_ACCEPTABLE);
        // }


        try{
            $user->password = Hash::make($request->password);
            $user->remember_token = null;
            if($user->save()){
                $token = $request->user()->currentAccessToken()->delete();
                $user->makeHidden('password');

                return response()->json(new DataResource(Response::HTTP_OK, 'Berhasil menyimpan password baru', $user), Response::HTTP_OK);
            } else{
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyimpan password baru'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch(Exception $e){
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyimpan password baru'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::whereHas('dataKaryawan', function ($query) use ($data) {
            $query->where('email', $data['email']);
        })->first();

        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna dengan email tersebut tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if (!$user->remember_token || $user->remember_token_expired_at < now()) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP sudah kadaluwarsa atau tidak ditemukan. Silakan lakukan permintaan ulang.'), Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($data['kode_otp'], $user->remember_token)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP tidak valid.'), Response::HTTP_UNAUTHORIZED);
        }

        // Setel ulang kata sandi pengguna
        $user->password = Hash::make($data['password']);
        $user->remember_token = null;
        $user->remember_token_expired_at = null;
        $user->save();

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kata sandi baru anda berhasil diubah.'), Response::HTTP_OK);
    }


}
