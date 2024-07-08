<?php

use App\Http\Controllers\CobaController;
use App\Http\Controllers\DataPersonalController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [LoginController::class, 'login']);
Route::get('/coba-face', [CobaController::class, 'compareFaces']);
Route::post('/password-reset', [PasswordResetController::class, 'passreset']);
Route::post('/check-password-reset', [PasswordResetController::class, 'checktoken']);
Route::post('/change-password-reset', [PasswordResetController::class, 'changepass']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/getuserinfo', [UserController::class, 'checkuser']);

    //data personal
    Route::post('/input-personal', [DataPersonalController::class, 'step1']);
    Route::post('/input-data-keluarga', [DataPersonalController::class, 'storekeluarga']);
    Route::get('/get-data-keluarga', [DataPersonalController::class, 'getkeluarga']);
    Route::post('/{dataKeluarga}/edit-data-keluarga', [DataPersonalController::class, 'updatekeluarga']);
    Route::post('/input-berkas', [DataPersonalController::class, 'step5']);
    Route::post('/input-personal-file', [DataPersonalController::class, 'storepersonalfile']);

    //check user active
    Route::get('/active-user-check', [DataPersonalController::class, 'checkuseractive']);
    // Route::post('/logout', [LoginController::class, 'logout']);

    //presensi
    Route::post('/user-store-presensi', [PresensiController::class, 'store']);

    //get jam kerja
    Route::get('/get-jam-kerja', [JamKerjaController::class, 'getjamkerja']);

});
