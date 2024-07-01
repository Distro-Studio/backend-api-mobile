<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'username' => ['required'],
            'password' => ['required'],
        ], [
            'username.required' => 'Username harus diisi',
            'password.required' => 'Password harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->messages()),Response::HTTP_NOT_ACCEPTABLE);
        }

        if(Auth::attempt($request->only('username', 'password'))){
            $user = User::where('id', Auth::user()->id)->with('roles')->first();
            // $findtoken = PersonalAccessToken::findToken($request->bearerToken());
            // $findtoken->delete();
            // $users = $findtoken->tokenable;
            // $users->currentAccessToken()->delete();
            // $role = $user->getRoleNames();

            $token = $user->createToken('TLogin')->plainTextToken;
            $user->arrtoken = [
                'token' => $token
            ];
            // $user->push($arrtoken);

            // if (Gate::denies('create.unitkerja'))
            // {
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu tidak bisa tambah unitkerja'), Response::HTTP_UNAUTHORIZED);
            // }else{
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu bisa tambah unitkerja'), Response::HTTP_UNAUTHORIZED);
            //     return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $role), Response::HTTP_OK);
            // }
            // if (Gate::check('isSAdmin'))
            // {
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu super admin'), Response::HTTP_UNAUTHORIZED);
            // }

            // if (Gate::check('isDirektur'))
            // {
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu direktur'), Response::HTTP_UNAUTHORIZED);
            // }
            // if (Gate::check('isAdmin'))
            // {
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu admin'), Response::HTTP_UNAUTHORIZED);
            // }
            // if (Gate::check('isKaryawan'))
            // {
            //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kamu Karyawan'), Response::HTTP_UNAUTHORIZED);
            // }

                // return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Logout Berhasil'), Response::HTTP_UNAUTHORIZED);
            return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $user), Response::HTTP_OK);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Email atau password salah'), Response::HTTP_UNAUTHORIZED);
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
