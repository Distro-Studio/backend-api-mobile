<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Cuti;
use App\Models\Notifikasi;
use App\Models\TipeCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CutiCotroller extends Controller
{
  public function getstatistik()
  {
    try {
      $tipecuti = TipeCuti::all();
      // $startDate = Carbon::now('Asia/Jakarta')->startOfMonth()->addDay();
      // $endDate = Carbon::now()->endOfMonth();

      // if ($request->tgl_mulai != null) {
      //     $startDate = Carbon::parse($request->tgl_mulai);
      // }

      // if ($request->tgl_selesai != null) {
      //     $endDate = Carbon::parse($request->tgl_selesai)->tomorrow();
      // }

      foreach ($tipecuti as $leaveType) {
        // $leaveType->used = Cuti::where('tipe_cuti_id', $leaveType->id)->where('status_cuti_id', 2)->where('user_id', Auth::user()->id)->whereBetween('created_at', [$startDate, $endDate])->count();
        $usedDays = Cuti::where('tipe_cuti_id', $leaveType->id)
          ->where('status_cuti_id', 4) // Only consider approved leaves
          ->where('user_id', Auth::user()->id)
          ->whereYear('created_at', Carbon::now()->year) // Within the current year
          ->get()
          ->sum(function ($cuti) {
            $tglFrom = Carbon::parse($cuti->tgl_from);
            $tglTo = Carbon::parse($cuti->tgl_to);
            return $tglFrom->diffInDays($tglTo) + 1;
          });
        $leaveType->used = $usedDays;
      }
      // return response()->json(new DataResource(Response::HTTP_OK, 'List statistik cuti berhasil didapatkan', $startDate), Response::HTTP_OK);
      return response()->json(new DataResource(Response::HTTP_OK, 'List statistik cuti berhasil didapatkan', $tipecuti), Response::HTTP_OK);
    } catch (\Exception $e) {
      // return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getriwayat(Request $request)
  {
    try {
      $query = Cuti::query();
      $query->where('user_id', Auth::user()->id);

      $tgl_mulai = Carbon::now('Asia/Jakarta')->startOfMonth();
      $tgl_selesai = Carbon::now('Asia/Jakarta')->endOfMonth();

      if ($request->tgl_mulai != null) {
        $tgl_mulai = Carbon::parse($request->tgl_mulai);
      }

      if ($request->tgl_selesai != null) {
        $tgl_selesai = Carbon::parse($request->tgl_selesai);
      }

      $query->whereBetween('created_at', [$tgl_mulai, $tgl_selesai]);

      if ($request->filled('jenis')) {
        $query->where('tipe_cuti_id', $request->jenis);
      }

      if ($request->filled('status')) {
        $query->where('status_cuti_id', $request->status);
      }

      $offset = 4;

      if ($request->filled('offset')) {
        $offset = $request->offset;
      }
      $query->with('tipeCuti', 'statuscuti');
      $cutis = $query->paginate($offset);
      // dd($query->toSql(), $query->getBindings());
      // dd($tgl_mulai);
      return response()->json(new DataResource(Response::HTTP_OK, 'List statistik cuti berhasil didapatkan', $cutis), Response::HTTP_OK);
    } catch (\Exception $e) {
      // return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function storecuti(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'tgl_mulai' => 'date|required',
      'tgl_selesai' => 'required|date',
      'jenis_cuti' => 'required',
    ], [
      'tgl_mulai.required' => 'Tanggal mulai tidak boleh kosong',
      'tgl_mulai.date' => 'Tanggal mulai harus berupa tanggal',
      'tgl_selesai.date' => 'Tanggal mulai harus berupa tanggal',
      'tgl_selesai.required' => 'Tanggal selesai tidak boleh kosong',
      'jenis_cuti.required' => 'Jenis/Tipe cuti tidak boleh kosong',
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors()), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    try {
      // $tipecuti = TipeCuti::where('id', $request->jenis)
      // Parse the start and end dates
      $startDate = Carbon::parse($request->tgl_mulai);
      $endDate = Carbon::parse($request->tgl_selesai);

      // Calculate the number of days requested
      $requestedDays = $startDate->diffInDays($endDate) + 1; // inclusive of start and end date

      // Get the leave type and its quota
      $leaveType = TipeCuti::findOrFail($request->jenis_cuti);
      $quota = $leaveType->kuota;

      // Calculate the total days used in the current year for this leave type
      $userId = Auth::id(); // Get the current authenticated user's ID

      $usedDays = Cuti::where('tipe_cuti_id', $leaveType->id)
        ->where('status_cuti_id', 2) // Only consider approved leaves
        ->where('user_id', $userId)
        ->whereYear('created_at', Carbon::now()->year) // Within the current year
        ->get()
        ->sum(function ($cuti) {
          $tglFrom = Carbon::parse($cuti->tgl_from);
          $tglTo = Carbon::parse($cuti->tgl_to);
          return $tglFrom->diffInDays($tglTo) + 1;
        });

      // Check if the requested days exceed the remaining quota
      if (($usedDays + $requestedDays) > $quota) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Kuota cuti sudah habis'), Response::HTTP_NOT_ACCEPTABLE);
      }

      $cutis = Cuti::create([
        'user_id' => Auth::user()->id,
        'tipe_cuti_id' => $request->jenis_cuti,
        'tgl_from' => $request->tgl_mulai,
        'tgl_to' => $request->tgl_selesai,
        'durasi' => $request->durasi,
        'status_cuti_id' => 1
      ]);

      $notifikasi = Notifikasi::create([
        'kategori_notifikasi_id' => 1,
        'user_id' => Auth::user()->id,
        'message' => 'Pengajuan Cuti ' . Auth::user()->nama,
        'is_read' => 0,
        'is_verifikasi' => 1,
      ]);

      return response()->json(new DataResource(Response::HTTP_OK, 'Cuti berhasil diajukan', $cutis), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getalltipecuti()
  {
    try {
      $tipecuti = TipeCuti::all();
      return response()->json(new DataResource(Response::HTTP_OK, 'List tipe cuti berhasil didapatkan', $tipecuti), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
