<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak sesuai',
            'password.required' => 'Password harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->messages()),Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            // Cari data karyawan berdasarkan email
            $dataKaryawan = DataKaryawan::where('email', $request->email)->first();
            // $authenticate = false;

            if (!$dataKaryawan) {
                // return response()->json(['error' => 'Email tidak ditemukan.'], 404);
                // $authenticate = false;
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Email atau password salah'), Response::HTTP_UNAUTHORIZED);
            }

            // Ambil user terkait
            $user = $dataKaryawan->user;
            $datauser = User::where('id', $user->id)->first();

            // Cek password
            if (!Hash::check($request->password, $datauser->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Email atau password salah'), Response::HTTP_UNAUTHORIZED);
            }

            // $cekuser = User::where('id', Auth::user()->id)->select('status_aktif')->first();


            if ($datauser->status_aktif == 1 && $datauser->data_completion_step == 0)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Akun anda tidak valid'), Response::HTTP_UNAUTHORIZED);
            }

            if ($datauser->status_aktif == 2 && $datauser->data_completion_step != 0)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Akun anda tidak valid'), Response::HTTP_UNAUTHORIZED);
            }

            $datauser->makeHidden('password');

            Auth::login($datauser);

            // Buat token atau lakukan tindakan lain setelah login berhasil
            $token = $user->createToken('TLogin')->plainTextToken;
            $users = User::where('id', Auth::user()->id)->with('roles')->first();
            $users->arrtoken = [
                'token' => $token
            ];

            $dataKaryawan = DataKaryawan::select('unit_kerja_id')->where('user_id', Auth::user()->id)->with('unitkerja')->first();
            $unitkerja = UnitKerja::where('id', $dataKaryawan->unit_kerja_id)->first();
            $users->unit_kerja = [
                $unitkerja,
            ];

            $users->makeHidden('password');

            return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $users), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal server error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // if(Auth::attempt($request->only('username', 'password'))){
        //     $user = User::where('id', Auth::user()->id)->with('roles')->first();
        //     // $findtoken = PersonalAccessToken::findToken($request->bearerToken());
        //     // $findtoken->delete();
        //     // $users = $findtoken->tokenable;
        //     // $users->currentAccessToken()->delete();
        //     // $role = $user->getRoleNames();

        //     $token = $user->createToken('TLogin')->plainTextToken;
        //     $user->arrtoken = [
        //         'token' => $token
        //     ];
        //     // $user->push($arrtoken);

        //     // if (Gate::denies('create.unitkerja'))
        //     // {
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu tidak bisa tambah unitkerja'), Response::HTTP_UNAUTHORIZED);
        //     // }else{
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu bisa tambah unitkerja'), Response::HTTP_UNAUTHORIZED);
        //     //     return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $role), Response::HTTP_OK);
        //     // }
        //     // if (Gate::check('isSAdmin'))
        //     // {
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu super admin'), Response::HTTP_UNAUTHORIZED);
        //     // }

        //     // if (Gate::check('isDirektur'))
        //     // {
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu direktur'), Response::HTTP_UNAUTHORIZED);
        //     // }
        //     // if (Gate::check('isAdmin'))
        //     // {
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu admin'), Response::HTTP_UNAUTHORIZED);
        //     // }
        //     // if (Gate::check('isKaryawan'))
        //     // {
        //     //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu Karyawan'), Response::HTTP_UNAUTHORIZED);
        //     // }

        //         // return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Logout Berhasil'), Response::HTTP_UNAUTHORIZED);
        //     return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $user), Response::HTTP_OK);
        // }

        // return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Email atau password salah'), Response::HTTP_UNAUTHORIZED);
    }

    public function logout(Request $request)
    {
        try{
            $token = $request->user()->currentAccessToken()->delete();
            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Logout berhasil'), Response::HTTP_OK);

        } catch(\Exception $e){
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Logout gagal'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
