<?php

use App\Http\Controllers\DataPersonalController;
use App\Http\Controllers\LoginController;
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
Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/getuserinfo', [UserController::class, 'checkuser']);

    //data personal
    Route::post('/input-personal-step1', [DataPersonalController::class, 'step1']);
    Route::post('/input-personal-step2', [DataPersonalController::class, 'step2']);
    Route::post('/input-personal-step3', [DataPersonalController::class, 'step3']);
    Route::post('/input-data-keluarga', [DataPersonalController::class, 'storekeluarga']);
    Route::get('/get-data-keluarga', [DataPersonalController::class, 'getkeluarga']);
    Route::post('/{dataKeluarga}/edit-data-keluarga', [DataPersonalController::class, 'updatekeluarga']);
    Route::post('/input-personal-step5', [DataPersonalController::class, 'step5']);
    Route::post('/input-personal-file', [DataPersonalController::class, 'storepersonalfile']);

    //check user active
    Route::get('/active-user-check', [DataPersonalController::class, 'checkuseractive']);
    // Route::post('/logout', [LoginController::class, 'logout']);
});
