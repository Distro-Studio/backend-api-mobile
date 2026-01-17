<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\DataKaryawan;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
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
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'Email atau username harus diisi',
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
                $ceknik = DataKaryawan::where('nik', $request->email)->first();
                // return response()->json(['error' => 'Email tidak ditemukan.'], 404);
                // $authenticate = false;
                if(!$ceknik) {
                    $usercek = User::where('username', $request->email)->first();
                    if (!$usercek) {
                        return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Email/username atau password salah'), Response::HTTP_UNAUTHORIZED);
                    }else {
                        $dataKaryawan = DataKaryawan::where('user_id', $usercek->id)->first();
                    }
                }else{
                    $dataKaryawan = $ceknik;
                }
            }

            // Ambil user terkait
            $user = $dataKaryawan->user;
            $datauser = User::where('id', $user->id)->first();

            // Cek password
            if (!Hash::check($request->password, $datauser->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Email/username atau password salah'), Response::HTTP_BAD_REQUEST);
            }

            // $cekuser = User::where('id', Auth::user()->id)->select('status_aktif')->first();


            if ($datauser->status_aktif == 1 && $datauser->data_completion_step == 0)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Akun anda belum aktif'), Response::HTTP_UNAUTHORIZED);
            }

            if ($datauser->status_aktif == 2 && $datauser->data_completion_step != 0)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Akun anda tidak valid'), Response::HTTP_UNAUTHORIZED);
            }

            if ($datauser->status_aktif == 3)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Akun anda tidak aktif'), Response::HTTP_UNAUTHORIZED);
            }

            $datauser->makeHidden('password');

            Auth::login($datauser);

            // Buat token atau lakukan tindakan lain setelah login berhasil
            $token = $user->createToken('TLogin')->plainTextToken;
            // $tokenResult = $user->createToken('TLogin');
            // $token = $tokenResult->plainTextToken;
            // $tokenExpiration = Carbon::now()->addHours(20);
            // $tokenResult->accessToken->expires_at = $tokenExpiration;
            // $tokenResult->accessToken->save();

            $users = User::where('id', Auth::user()->id)->with('roles')->with('fotoprofil')->first();
            $users->arrtoken = [
                'token' => $token
            ];

            $dataKaryawan = DataKaryawan::where('user_id', Auth::user()->id)->with('unitkerja')->first();
            $unitkerja = UnitKerja::where('id', $dataKaryawan->unit_kerja_id)->first();
            $users->unit_kerja = [
                $unitkerja,
            ];

            if($users->foto_profil) {
                $users->fotoprofil->path = 'https://192.168.0.20/RskiSistem24/file-storage/public'.$users->fotoprofil->path;
            }


            $users->makeHidden('password');

            $duesip = null;
            $duestr = null;

            // Ambil tanggal masa_berlaku_str dari database
            if ($dataKaryawan->masa_berlaku_str != null) {
                // Hitung tanggal 6 bulan sebelumnya
                $reminderStr = Carbon::createFromFormat('d-m-Y', $dataKaryawan->masa_berlaku_str)
                    ->subMonths(7); // Tanggal 6 bulan sebelum masa_berlaku_str
                
                // Cek apakah tanggal hari ini lebih besar (lebih awal) dari reminderStr
                if (Carbon::now()->greaterThan($reminderStr)) {
                    // Jika ya, lakukan sesuatu (misalnya peringatan atau penyesuaian)
                    // Anda bisa mengatur status atau flag di sini
                    // $statusReminder = "Tanggal masa berlaku sudah lewat 6 bulan.";
                    $duestr = $dataKaryawan->masa_berlaku_str;
                } else {
                    $duestr = null;
                }
            }

            // Cek masa_berlaku_sip dan lakukan hal yang sama jika diperlukan
            if ($dataKaryawan->masa_berlaku_sip != null) {
                $reminderSip = Carbon::createFromFormat('d-m-Y', $dataKaryawan->masa_berlaku_sip)
                    ->subMonths(3); // Tanggal 3 bulan sebelum masa_berlaku_sip
                
                if (Carbon::now()->greaterThan($reminderSip)) {
                    // Jika ya, lakukan sesuatu
                    // $statusSip = "Tanggal SIP sudah lewat 3 bulan.";
                    $duesip = $dataKaryawan->masa_berlaku_sip;
                } else {
                    $duesip = null;
                    // $statusSip = "Tanggal SIP masih dalam jangka waktu yang valid.";
                }
            }

            // Menyimpan hasil reminder ke objek users untuk dikirimkan sebagai respons
            $users->masa_berlaku_str = $duestr;
            $users->masa_berlaku_sip = $duesip;

            return response()->json(new DataResource(Response::HTTP_OK, 'Login Berhasil', $users), Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
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
