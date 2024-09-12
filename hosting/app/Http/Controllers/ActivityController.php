<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    public function getpresensiactivity(Request $request)
    {
        try {
            $tgl_mulai = Carbon::now()->startOfWeek();
            $tgl_selesai = Carbon::now()->endOfWeek();
            $jenis = null;

            if($request->tgl_mulai){
                // $tgl_mulai = $request->tgl_mulai;
                $tgl_mulai = Carbon::parse($request->tgl_mulai)->startOfDay()->format('Y-m-d H:i:s');
            }

            if($request->tgl_selesai){
                // $tgl_selesai = $request->tgl_selesai;
                $tgl_selesai = Carbon::parse($request->tgl_selesai)->endOfDay()->format('Y-m-d H:i:s');
            }

            if($request->jenis){
                $activity = ActivityLog::where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai])->where('activity', $request->jenis)->get();
            }else{
                $activity = ActivityLog::where('user_id', Auth::user()->id)->whereBetween('created_at', [$tgl_mulai, $tgl_selesai])->get();
            }

            return response()->json(new DataResource(Response::HTTP_OK, 'List aktivitas presensi berhasil didapatkan', $activity), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
