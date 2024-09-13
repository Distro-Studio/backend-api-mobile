<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\CobaController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\CutiCotroller;
use App\Http\Controllers\DataPersonalController;
use App\Http\Controllers\DiklatController;
use App\Http\Controllers\GetListController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PerubahannDataController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TukarJadwalController;
use App\Http\Controllers\UserController;
use App\Models\Berkas;
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

Route::get('/cobadownload', [CobaController::class, 'cobadownload']);



Route::middleware(['auth:sanctum'])->group(function () {
  Route::post('/change-password-reset', [PasswordResetController::class, 'changepass']);
  Route::post('/logout', [LoginController::class, 'logout']);
  Route::get('/getuserinfo', [UserController::class, 'checkuser']);

  //global request
  Route::get('get-list-agama', [GetListController::class, 'getlistagama']);
  Route::get('/get-list-goldar', [GetListController::class, 'getlistgoldar']);

  //data personal
  Route::post('/input-personal', [DataPersonalController::class, 'step1']);
  Route::post('/input-keluarga', [DataPersonalController::class, 'storekeluarga']);
  Route::get('/get-data-keluarga', [DataPersonalController::class, 'getkeluarga']);
  Route::post('/{dataKeluarga}/edit-data-keluarga', [DataPersonalController::class, 'updatekeluarga']);
  Route::post('/input-berkas', [DataPersonalController::class, 'step5']);
  Route::post('/input-personal-file', [DataPersonalController::class, 'storepersonalfile']);

  //check user active
  Route::get('/active-user-check', [DataPersonalController::class, 'checkuseractive']);
  // Route::post('/logout', [LoginController::class, 'logout']);

  //presensi
  Route::post('/check-in-presensi', [PresensiController::class, 'store']);
  Route::post('/check-out-presensi', [PresensiController::class, 'checkoutpresensi']);

  //get jadwal
  Route::get('/get-today-jadwal', [JadwalController::class, 'gettodayjadwal']);
  Route::post('/get-jadwal', [JadwalController::class, 'getalljadwal']);

  //activity presensi
  Route::get('/get-activity-presensi', [PresensiController::class, 'getactivity']);

  //counting
  Route::get('/count-pending', [CountController::class, 'countTukarandLembur']);

  //get latest pengumuman

  //get another karyawan with same schedule
  Route::get('/{jadwals}/get-karyawan-same-jadwal', [JadwalController::class, 'getanotherkaryawan']);
  Route::get('/{user}/get-jadwal-karyawan', [JadwalController::class, 'getuserjadwal']);
  Route::post('/change-schedule', [JadwalController::class, 'changeschedule']);

  //get another karyawan with same unit kerja
  Route::post('/user-unit-kerja', [GetListController::class, 'getkaryawanunitkerja']);

  //get jadwal yang akan ditukar
  Route::get('/get-jadwal/{jadwal}/ditukar', [JadwalController::class, 'getjadwalditukar']);

  //presensi activity
  Route::post('/get-presensi-activity', [ActivityController::class, 'getpresensiactivity']);

  Route::get('/get-statistik-cuti', [CutiCotroller::class, 'getstatistik']);

  Route::post('/get-riwayat-cuti', [CutiCotroller::class, 'getriwayat']);

  Route::post('/store-cuti', [CutiCotroller::class, 'storecuti']);

  Route::post('/get-pengajuan-swap', [TukarJadwalController::class, 'getpengajuan']);
  Route::post('/get-permintaan-swap', [TukarJadwalController::class, 'getpermintaan']);

  Route::get('/get-statistik-lembur', [LemburController::class, 'getstatistik']);
  Route::post('/get-riwayat-lembur', [LemburController::class, 'getriwayat']);

  Route::post('/store-berkas-karyawan', [BerkasController::class, 'storeberkas']);
  Route::get('/get-all-berkas-karyawan', [BerkasController::class, 'getallberkas']);
  Route::post('/rename-berkas-karyawan', [BerkasController::class, 'renameberkas']);
  Route::post('/download-berkas-karyawan', [BerkasController::class, 'downloadberkas']);

  Route::post('/store-laporan', [LaporanController::class, 'storelaporan']);

  Route::get('/get-data-karyawan-personal', [PerubahannDataController::class, 'getdatapersonal']);

  Route::get('/get-detail-karyawan', [DataPersonalController::class, 'getdetailkaryawan']);

  Route::get('/get-berkas-karyawan-personal', [DataPersonalController::class, 'getberkaspersonal']);

  Route::post('/cek-password', [DataPersonalController::class, 'cekpassword']);
  Route::post('/get-detail-gaji', [DataPersonalController::class, 'getdetailpass']);

  Route::get('/get-all-status-karyawan', [StatusController::class, 'getallstatuskaryawan']);

  Route::post('/update-data-personal', [DataPersonalController::class, 'updatedatapersonal']);

  Route::post('/update-data-keluarga', [DataPersonalController::class, 'updatedatakeluarga']);

  Route::get('/get-list-tipecuti', [CutiCotroller::class, 'getalltipecuti']);

  Route::post('/get-pengumuman', [GetListController::class, 'getpengumuman']);

  Route::get('/get-data-karyawan', [DataPersonalController::class, 'getdatakaryawandetail']);

  Route::get('/get-notifikasi', [GetListController::class, 'getlistnotifikasi']);

  Route::get('/get-riwayat-perubahan', [GetListController::class, 'getriwayatperubahan']);

  // Route::get('/data-karyawan', []);

  Route::get('/get-all-diklat', [GetListController::class, 'getalldiklat']);

  Route::get('/{diklat}/get-detail-diklat', [DiklatController::class, 'getdetail']);

  Route::post('/join-diklat', [DiklatController::class, 'joindiklat']);

  Route::post('/get-riwayat-izin', [IzinController::class, 'getriwayat']);

  Route::post('/store-izin', [IzinController::class, 'store']);
  Route::post('/get-izin', [IzinController::class, 'getriwayat']);
  Route::get('/export-slip-gaji', [GetListController::class, 'exportslip']);

  /**
   * diklat :
   * file, nama_acara
   */

  Route::post('/store-diklat', [DiklatController::class, 'storediklat']);
  Route::get('/get-riwayat-diklat', [DiklatController::class, 'getriwayat']);

});
