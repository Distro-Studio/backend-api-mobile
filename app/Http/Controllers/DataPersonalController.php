<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\Penggajian;
use App\Models\PerubahanKeluarga;
use App\Models\RiwayatPerubahan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DataPersonalController extends Controller
{
  public function step1(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'nama' => 'max:255',
      'tempat_lahir' => 'required',
      'tanggal_lahir' => 'required|date',
      'no_hp' => 'required|numeric',
      'jenis_kelamin' => 'required|in:0,1,2',
      'nik_ktp' => 'required',
      'no_kk' => 'required',
      'agama' => 'required',
      'golongan_darah' => 'required',
      'tinggi_badan' => 'required|integer',
      'berat_badan' => 'required',
      'alamat' => 'required',
      'tahun_lulus' => 'required|numeric',
      'no_ijazah' => 'required',
      'pendidikan_terakhir' => 'required'
    ], [
      // 'nama.required' => 'Nama harus diisi',
      'nama.max' => 'Maksimal 255 kata',
      'tempat_lahir.required' => 'Tempat lahir harus diisi',
      'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
      'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal',
      'no_hp.required' => 'No telfon harus di isi',
      'no_hp.numeric' => 'No telfon harus berupa angka',
      'jenis_kelamin.required' => 'Jenis kelamin harus diisi',
      'jenis_kelamin.in' => 'Jenis kelamin harus Pria atau Wanita',
      'nik_ktp.required' => 'Nomor Induk Kependudukan harus diisi',
      //   'nik_ktp.integer' => 'Nomor Induk Kependudukan harus berupa angka',
      //   'nik_ktp.digits' => 'Nomor Induk Kependudukan harus terdiri dari 16 digit',
      'no_kk.required' => 'Nomor Kartu Keluarga harus di isi',
      //   'no_kk.integer' => 'Nomor Kartu Keluarga harus berupa angka',
      //   'no_kk.digits' => 'Nomor Kartu Keluarga harus terdiri dari 16 digit',
      'agama.required' => 'Agama harus diisi',
      'golongan_darah.required' => 'Golongan darah harus diisi',
      'tinggi_badan.required' => 'Tinggi badan harus diisi',
      'tinggi_badan.integer' => 'Tinggi badan harus berupa angka',
      'berat_badan.required' => 'Berat badan harus diisi',
      'alamat.required' => 'Alamat harus diisi',
      'tahun_lulus.required' => 'Tahun lulus harus diisi',
      'tahun_lulus.numeric' => 'Tahun lulus harus berupa angka',
      'no_ijazah.required' => 'Nomor ijazah harus diisi',
      'pendidikan_terakhir.required' => 'Pendidikan terakhir harus diisi',
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    try {
      $user = User::where('id', Auth::user()->id)->first();
      $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

      if (!$user) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Akun user tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      if (!$data) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      $nama = null;
      if ($request->nama) {
        $user->name = $request->nama;
        $user->save();
      }

      // Mengambil berat dan tinggi dari input form
      $weight = $request->berat_badan;
      $height = $request->tinggi_badan / 100; // Konversi cm ke meter

      // Rumus BMI
      $bmi = $weight / ($height * $height);

      // Menentukan kategori BMI berdasarkan hasil perhitungan
      if ($bmi < 18.5) {
          $category = 'Berat badan kurang';
      } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
          $category = 'Berat badan normal';
      } elseif ($bmi >= 25 && $bmi <= 29.9) {
          $category = 'Berat badan berlebih (overweight)';
      } else {
          $category = 'Obesitas';
      }

      //   $dataKaryawan = DataKaryawan::where('user_id', Auth::user()->id)->update([
      //     'tempat_lahir' => $request->tempat_lahir,
      //     'tgl_lahir' => $request->tanggal_lahir,
      //     'no_hp' => $request->no_hp,
      //     'jenis_kelamin' => $request->jenis_kelamin,
      //     'nik_ktp' => $request->nik_ktp,
      //     'no_kk' => $request->no_kk,
      //     'kategori_agama_id' => $request->agama,
      //     'kategori_darah_id' => $request->golongan_darah,
      //     'tinggi_badan' => $request->tinggi_badan,
      //     'alamat' => $request->alamat,
      //     'tahun_lulus' => $request->tahun_lulus,
      //   ]);

      $data->tempat_lahir = $request->tempat_lahir;
      $data->tgl_lahir = $request->tanggal_lahir;
      $data->no_hp = $request->no_hp;
      $data->jenis_kelamin = $request->jenis_kelamin;
      $data->nik_ktp = $request->nik_ktp;
      $data->no_kk = $request->no_kk;
      $data->kategori_agama_id = $request->agama;
      $data->kategori_darah_id = $request->golongan_darah;
      $data->tinggi_badan = $request->tinggi_badan;
      $data->berat_badan = $request->berat_badan;
      $data->gelar_depan = $request->gelar_depan;
      $data->bmi_value = $bmi;
      $data->bmi_ket = $category;
      $data->alamat = $request->alamat;
      $data->no_ijazah = $request->no_ijazah;
      $data->tahun_lulus = $request->tahun_lulus;
      $data->pendidikan_terakhir = $request->pendidikan_terakhir;
      $data->save();

      $user->data_completion_step = 2;
      $user->save();

      // dd(DataKaryawan::where('user_id', Auth::user()->id)->first());


      return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', Auth::user()->id), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function storekeluarga(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'keluarga' => 'required',
    ], [
      'keluarga.required' => 'Keluarga harus diisi',
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    try {
      $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

      if (!$data) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      $keluarga = json_encode($request->keluarga, JSON_UNESCAPED_SLASHES);
      $datakeluarga = json_decode(stripslashes($keluarga), true);

      foreach ($datakeluarga['keluarga'] as $k) {
        $keluarga = DataKeluarga::create([
            'data_karyawan_id' => $data->id,
            'nama_keluarga' => $k['nama_keluarga'],
            'hubungan' => $k['hubungan']['value'],
            'pendidikan_terakhir' => $k['pendidikan_terakhir'],
            'status_hidup' => $k['status_hidup']['value'],
            'pekerjaan' => $k['pekerjaan'],
            'no_hp' => $k['no_hp'],
            'email' => $k['email'],
            'is_bpjs' => $k['is_bpjs'],
            'status_keluarga_id' => 1,
            'verifikator_1' => null,
        ]);
        // return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $k), Response::HTTP_OK);

      }

      $user = User::where('id', Auth::user()->id)->update(['data_completion_step' => 3]);

      return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $keluarga), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  }

  public function getkeluarga()
  {
    try {
      //code...
      $karyawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
      $data = DataKeluarga::where('data_karyawan_id', $karyawan->id)->with('pendidikanTerakhir')->get();
      if ($data->isEmpty()) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      $keluarga = $data->toArray();

      return response()->json(new DataResource(Response::HTTP_OK, 'Data keluarga ' . Auth::user()->nama, $keluarga), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updatekeluarga(DataKeluarga $dataKeluarga, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'nama_keluarga' => 'required',
      'hubungan' => 'required',
      'pendidikan_terakhir' => 'required',
      'status_hidup' => 'required',
    ], [
      'nama_keluarga.required' => 'Nama harus diisi',
      'hubungan.required' => 'Hubungan keluarga harus diisi',
      'pendidikan_terakhir.required' => 'Pendidikan terakhir harus diisi',
      'status_hidup.required' => 'Status hidup harus diisi',
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    try {
      $dataKeluarga->nama_keluarga = $request->nama_keluarga;
      $dataKeluarga->hubungan = $request->hubungan;
      $dataKeluarga->pendidikan_terakhir = $request->pendidikan_terakhir;
      $dataKeluarga->status_hidup = $request->status_hidup;
      $dataKeluarga->pekerjaan = $request->pekerjaan;
      $dataKeluarga->no_hp = $request->no_hp;
      $dataKeluarga->email = $request->email;
      $dataKeluarga->save();

      return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $dataKeluarga), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function step5(Request $request)
  {
    $validator = Validator::make($request->all(), [
    //   'no_str' => 'required',
      //   'masa_berlaku_str' => 'required',
    //   'no_sip' => 'required',
      //   'masa_berlaku_sip' => 'required',
    //   'no_bpjsksh' => 'required',
    //   'no_bpjsktk' => 'required',
      'npwp' => 'required'
    ], [
    //   'no_str.required' => 'Nomor STR harus diisi',
      //   'masa_berlaku_str.required' => 'Masa berlaku STR harus diisi',
    //   'no_sip.required' => 'Nomor SIP harus diisi',
      //   'masa_berlaku_sip.required' => 'Masa berlaku SIP harus diisi',
    //   'no_bpjsksh.required' => 'Nomor BPJS Kesehatan harus diisi',
    //   'no_bpjsktk.required' => 'Nomor BPJS Ketenagakerjaan harus diisi',
      'npwp.required' => 'NPWP harus diisi'
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

    if (!$data) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
    }

    // return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan', $request->all()), Response::HTTP_NOT_FOUND);

    $data->no_str = $request->no_str;
    $data->masa_berlaku_str = $request->masa_berlaku_str;
    $data->no_sip = $request->no_sip;
    $data->masa_berlaku_sip = $request->masa_berlaku_sip;
    $data->no_bpjsksh = $request->no_bpjsksh;
    $data->no_bpjsktk = $request->no_bpjsktk;
    $data->npwp = $request->npwp;
    $data->save();

    $user = User::where('id', Auth::user()->id)->update(['data_completion_step' => 5]);

    return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $request->all()), Response::HTTP_OK);
  }

  public function formatBytes($bytes, $precision = 2)
  {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
  }

  public function storepersonalfile(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'ktp' => 'required|file',
      'kk' => 'required|file',
      'sip' => 'required|file',
      'bpjs_kesehatan' => 'required|file',
      'bpjs_ketenagakerjaan' => 'required|file',
      'ijazah' => 'required|file',
      'sertifikat' => 'required|file',
    ], [
      'ktp.required' => 'Ktp harus diisi',
      'ktp.file' => 'Ktp harus berupa file',
      'kk.required' => 'Kartu keluarga harus diisi',
      'kk.file' => 'Kartu keluarga harus berupa file',
      'sip.required' => 'SIP harus diisi',
      'sip.file' => 'SIP harus berupa file',
      'bpjs_kesehatan.required' => 'BPJS Kesehatan harus diisi',
      'bpjs_kesehatan.file' => 'BPJS Kesehatan harus berupa file',
      'bpjs_ketenagakerjaan.required' => 'BPJS Ketenagakerjaan harus diisi',
      'bpjs_ketenagakerjaan.file' => 'BPJS Ketenagakerjaan harus berupa file',
      'ijazah.required' => 'Ijazah harus diisi',
      'ijazah.file' => 'Ijazah harus berupa file',
      'sertifikat.required' => 'Sertifikat harus diisi',
      'sertifikat.file' => 'Sertifikat harus berupa file',
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    try {
      $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
      //KTP
      $uploadktp = StorageFileHelper::uploadToServer($request, Str::random(8), 'ktp');

      $cekberkasktp = Berkas::where('nama', 'KTP')->where('user_id', Auth::user()->id)->delete();

      $berkasktp = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadktp['id_file']['id'],
        'nama' => 'KTP',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadktp['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadktp['nama_file'],
        'ext' => $uploadktp['ext'],
        'size' => $uploadktp['size'],
      ]);

      if (!$berkasktp) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas KTP gagal di upload'), Response::HTTP_BAD_REQUEST);
      }




      //KK
      $uploadkk = StorageFileHelper::uploadToServer($request, Str::random(8), 'kk');

      $cekberkaskk = Berkas::where('nama', 'KK')->where('user_id', Auth::user()->id)->delete();

      $berkaskk = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadkk['id_file']['id'],
        'nama' => 'KK',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadkk['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadkk['nama_file'],
        'ext' => $uploadkk['ext'],
        'size' => $uploadkk['size'],
      ]);

      if (!$berkaskk) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas KK gagal di upload'), Response::HTTP_BAD_REQUEST);
      }


      //SIP
      $uploadsip = StorageFileHelper::uploadToServer($request, Str::random(8), 'sip');

      $cekberkasktp = Berkas::where('nama', 'SIP')->where('user_id', Auth::user()->id)->delete();

      $berkassip = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadsip['id_file']['id'],
        'nama' => 'SIP',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadsip['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadsip['nama_file'],
        'ext' => $uploadsip['ext'],
        'size' => $uploadsip['size'],
      ]);

      if (!$berkassip) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas SIP gagal di upload'), Response::HTTP_BAD_REQUEST);
      }


      //BPJS Kesehatan
      $uploadbpjs = StorageFileHelper::uploadToServer($request, Str::random(8), 'bpjs_kesehatan');

      $cekberkasktp = Berkas::where('nama', 'BPJS Kesehatan')->where('user_id', Auth::user()->id)->delete();

      $berkasbpjs = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadbpjs['id_file']['id'],
        'nama' => 'BPJS Kesehatan',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadbpjs['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadbpjs['nama_file'],
        'ext' => $uploadbpjs['ext'],
        'size' => $uploadbpjs['size'],
      ]);

      if (!$berkasbpjs) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas BPJS Kesehatan gagal di upload'), Response::HTTP_BAD_REQUEST);
      }

      //BPJS Ketenagakerjaan
      $uploadbpjstk = StorageFileHelper::uploadToServer($request, Str::random(8), 'bpjs_ketenagakerjaan');

      $cekberkasktp = Berkas::where('nama', 'BPJS Ketenagakerjaan')->where('user_id', Auth::user()->id)->delete();

      $berkasbpjstk = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadbpjstk['id_file']['id'],
        'nama' => 'BPJS Ketenagakerjaan',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadbpjstk['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadbpjstk['nama_file'],
        'ext' => $uploadbpjstk['ext'],
        'size' => $uploadbpjstk['size'],
      ]);

      if (!$berkasbpjstk) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas BPJS Ketenagakerjaan gagal di upload'), Response::HTTP_BAD_REQUEST);
      }

      //Ijazah
      $uploadijazah = StorageFileHelper::uploadToServer($request, Str::random(8), 'ijazah');

      $cekberkasktp = Berkas::where('nama', 'Ijazah')->where('user_id', Auth::user()->id)->delete();

      $berkasijazah = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadijazah['id_file']['id'],
        'nama' => 'Ijazah',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadijazah['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadijazah['nama_file'],
        'ext' => $uploadijazah['ext'],
        'size' => $uploadijazah['size'],
      ]);

      if (!$berkasijazah) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas Ijazah gagal di upload'), Response::HTTP_BAD_REQUEST);
      }

      //Sertifikat
      $uploadsertifikat = StorageFileHelper::uploadToServer($request, Str::random(8), 'sertifikat');

      $cekberkasktp = Berkas::where('nama', 'Sertifikat')->where('user_id', Auth::user()->id)->delete();

      $berkassertifikat = Berkas::create([
        'user_id' => Auth::user()->id,
        'file_id' => $uploadsertifikat['id_file']['id'],
        'nama' => 'Sertifikat',
        'kategori_berkas_id' => 1,
        'status_berkas_id' => 1,
        'path' => $uploadsertifikat['path'],
        'tgl_upload' => date('Y-m-d'),
        'nama_file' => $uploadsertifikat['nama_file'],
        'ext' => $uploadsertifikat['ext'],
        'size' => $uploadsertifikat['size'],
      ]);

      if (!$berkassertifikat) {
        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas Sertifikat gagal di upload'), Response::HTTP_BAD_REQUEST);
      }

      $user = User::where('id', Auth::user()->id)->update(['data_completion_step' => 5]);

      return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berkas berhasil di upload'), Response::HTTP_OK);

    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // return response()->json(new DataResource(Response::HTTP_OK, 'Berkas berhasil di upload', $berkas), Response::HTTP_OK);
  }

  public function checkuseractive()
  {
    $user = User::where('id', Auth::user()->id)->first();

    if (!$user) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
    }

    if ($user->data_completion_step == 0) {
      return response()->json(new DataResource(Response::HTTP_FORBIDDEN, 'Akun anda sedang tidak aktif', ['data_completion_step' => false]), Response::HTTP_FORBIDDEN);
    }

    if ($user->data_completion_step == 1) {
      return response()->json(new DataResource(Response::HTTP_OK, 'Akun anda sedang tidak aktif', ['data_completion_step' => true]), Response::HTTP_FORBIDDEN);
    }
  }

  public function getdetailkaryawan()
  {
    $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
    $data = [
      'tempat_lahir' => $datakaryawan->tempat_lahir,
      'tgl_lahir' => $datakaryawan->tgl_lahir,
      'no_hp' => $datakaryawan->no_hp,
      'jenis_kelamin' => $datakaryawan->jenis_kelamin,
      'nik_ktp' => $datakaryawan->nik_ktp,
      'no_kk' => $datakaryawan->no_kk
    ];
  }

  public function getberkaspersonal()
  {
    $berkas = Berkas::where('nama', 'KTP')
      ->orWhere('nama', 'KK')
      ->orWhere('nama', 'SIP')
      ->orWhere('nama', 'BPJS Kesehatan')
      ->orWhere('nama', 'BPJS Ketenagakerjaan')
      ->orWhere('nama', 'Ijazah')
      ->orWhere('nama', 'Sertifikat')
      ->where('user_id', Auth::user()->id)
      ->get();

    $data = $berkas->map(function ($i) {
      return [
        'url' => env('URL_STORAGE') . $i->path,
        'name' => $i->nama,
        'ext' => StorageFileHelper::getExtensionFromMimeType($i->ext),
      ];
    });
    return response()->json(new DataResource(Response::HTTP_OK, 'Berkas berhasil di ambil', $data), Response::HTTP_OK);
  }

  public function cekpassword(Request $request)
  {
    $user = User::where('id', Auth::user()->id)->first();
    if (!Hash::check($request->password, $user->password)) {
      return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Email atau password salah'), Response::HTTP_BAD_REQUEST);
    }

    $user->makeHidden('password');

    return response()->json(new DataResource(Response::HTTP_OK, 'Password berhasil terkonfirmasi', $user));
  }

  public function getdetailpass(Request $request)
  {
    $user = User::where('id', Auth::user()->id)->with('dataKaryawan')->first();
    $penggajian = Penggajian::with([
      'detail_gajis',
      'data_karyawans.user',
      'data_karyawans.unitkerja',
      'data_karyawans.kelompok_gaji',
      'data_karyawans.ptkp'
    ])
      ->where('data_karyawan_id', $user->dataKaryawan->id)
      ->whereMonth('created_at', $request->bulan)
      ->whereYear('created_at', $request->tahun)
      ->first();

    if (!$penggajian) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $dataKaryawan = $penggajian->data_karyawans;
    $user = $dataKaryawan->user;
    $unitKerja = $dataKaryawan->unitkerja;
    $kelompokGaji = $dataKaryawan->kelompok_gaji;
    $ptkp = $dataKaryawan->ptkp;

    $detailGajis = $penggajian->detail_gajis->map(function ($detail) {
      return [
        'kategori_gaji' => $detail->kategori_gajis,
        'nama_detail' => $detail->nama_detail,
        'besaran' => $detail->besaran,
        'created_at' => $detail->created_at,
        'updated_at' => $detail->updated_at
      ];
    });

    $formattedData = [
      'user' => [
        'id' => $user->id,
        'nama' => $user->nama,
        'email_verified_at' => $user->email_verified_at,
        'data_karyawan_id' => $user->data_karyawan_id,
        'foto_profil' => $user->foto_profil,
        'data_completion_step' => $user->data_completion_step,
        'status_aktif' => $user->status_aktif,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
      ],
      'unit_kerja' => $unitKerja,
      'kelompok_gaji' => $kelompokGaji,
      'ptkp' => $ptkp,
      'detail_gaji' => $detailGajis,
      'take_home_pay' => $penggajian->take_home_pay,
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail gaji karyawan '{$user->nama}' berhasil ditampilkan.",
      'data' => $formattedData
    ], Response::HTTP_OK);
  }

  public function updatedatapersonal(Request $request)
  {
    $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();

    if (!$datakaryawan) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    try {
      $originaldata = null;
      $updateddata = $request->value_diubah;

      if ($request->kolom_diubah == 'tempat_lahir') {
        $originaldata = $datakaryawan->tempat_lahir;
      }

      if ($request->kolom_diubah == 'tgl_lahir') {
        $originaldata = $datakaryawan->tgl_lahir;
      }

      if ($request->kolom_diubah == 'no_hp') {
        $originaldata = $datakaryawan->no_hp;
      }

      if ($request->kolom_diubah == 'jenis_kelamin') {
        $originaldata = $datakaryawan->jenis_kelamin;
        $updateddata = $request->value_diubah['value'];
      }

      if ($request->kolom_diubah == 'nik_ktp') {
        $originaldata = $datakaryawan->nik_ktp;
      }

      if ($request->kolom_diubah == 'no_kk') {
        $originaldata = $datakaryawan->no_kk;
      }

      if ($request->kolom_diubah == 'kategori_agama_id') {
        $originaldata = $datakaryawan->kategori_agama_id;
        $updateddata = $request->value_diubah['value'];
      }

      if ($request->kolom_diubah == 'kategori_darah_id') {
        $originaldata = $datakaryawan->kategori_darah_id;
      }

      if ($request->kolom_diubah == 'tinggi_badan') {
        $originaldata = $datakaryawan->tinggi_badan;
      }

      if ($request->kolom_diubah == 'berat_badan') {
        $originaldata = $datakaryawan->berat_badan;
      }

      if ($request->kolom_diubah == 'alamat') {
        $originaldata = $datakaryawan->alamat;
      }

      if ($request->kolom_diubah == 'no_ijasah') {
        $originaldata = $datakaryawan->no_ijasah;
      }

      if ($request->kolom_diubah == 'tahun_lulus') {
        $originaldata = $datakaryawan->tahun_lulus;
      }

      if ($request->kolom_diubah == 'pendidikan_terakhir') {
        $originaldata = $datakaryawan->pendidikan_terakhir;
      }

      if ($request->kolom_diubah == 'gelar_depan') {
        $originaldata = $datakaryawan->gelar_depan;
      }

      if($request->asal_sekolah == 'asal_sekolah') {
        $originaldata = $datakaryawan->asal_sekolah;
      }

      if($request->gelar_belakang == 'gelar_belakang') {
        $originaldata = $datakaryawan->gelar_belakang;
      }


      $datadiubah = RiwayatPerubahan::create([
        'data_karyawan_id' => $datakaryawan->id,
        'jenis_perubahan' => 'Personal',
        'kolom' => $request->kolom_diubah,
        'original_data' => $originaldata,
        'updated_data' => $updateddata,
        'status_perubahan_id' => 1,
        'updated_at' => null
      ]);


      return response()->json(new DataResource(Response::HTTP_OK, 'Perubahan berhasil disimpan', $datadiubah), Response::HTTP_OK);
    //   return response()->json(new DataResource(Response::HTTP_OK, 'Perubahan berhasil disimpan', $request->value_diubah['value']), Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updatedatakeluarga(Request $request)
  {
    $datakaryawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
    $datakeluargori = DataKeluarga::where('data_karyawan_id', $datakaryawan->id)->with('pendidikanTerakhir')->get();
    // $keluarga = json_encode($request->keluarga, JSON_UNESCAPED_SLASHES);
    // $datakeluarga = json_decode(stripslashes($keluarga), true);
    // $keluarga = json_encode($request->keluarga);
    $datakeluarga = json_decode($request->value_diubah, true);
    $formatedData = [];
    $updated_data = [];
    if(!$datakeluargori->isEmpty()){
        $formatedData = $datakeluargori->map(function($item){
            $labelstatus = 'Hidup';
            if ($item->status_hidup == 0) {
                $labelstatus = 'Meninggal';
            }
            return[
                'data_keluarga_id' => $item->id,
                'hubungan' => $item->hubungan,
                'nama_keluarga' => $item->nama_keluarga,
                'status_hidup' => $item->status_hidup,
                'pendidikan_terakhir' => $item->pendidikanTerakhir->id,
                'pekerjaan' => $item->pekerjaan,
                'no_hp' => $item->no_hp,
                'email' => $item->email,
                'is_bpjs' => $item->is_bpjs,
                'id' => $item->id
            ];
        });
    }

    if (!empty($datakeluarga)) {
      foreach ($datakeluarga as $keluargaItem) {
          $statushidup = 1;
          $isbpjs = 1;
          if($keluargaItem['status_hidup']['value']){
            $statushidup = 1;
          }else {
            $statushidup = 0;
          }

          if($keluargaItem['is_bpjs']){
            $isbpjs = 1;
          }else {
            $isbpjs = 0;
          }
          $updated_data[] = [
              'data_keluarga_id' => $keluargaItem['data_keluarga_id'] ?? null,
              'hubungan' => $keluargaItem['hubungan']['value'],
              'nama_keluarga' => $keluargaItem['nama_keluarga'],
              'status_hidup' => $statushidup,
              'pendidikan_terakhir' => $keluargaItem['pendidikan_terakhir']['value'],
              'pekerjaan' => $keluargaItem['pekerjaan'],
              'no_hp' => $keluargaItem['no_hp'],
              'email' => $keluargaItem['email'],
              'is_bpjs' => $isbpjs,
              'id' => $keluargaItem['id'] ?? null
          ];
      }
  }

  if($formatedData == null) {
      $original = null;
  } else {
      $original = json_encode($formatedData);
  }

  if($updated_data == null) {
      $update = null;
  } else {
      $update = json_encode($updated_data);
  }
    $datadiubah = RiwayatPerubahan::create([
      'data_karyawan_id' => $datakaryawan->id,
      'jenis_perubahan' => 'Keluarga',
      'kolom' => 'Data Keluarga',
      'original_data' => $original,
      'updated_data' => $update,
      'status_perubahan_id' => 1,
    ]);

    // return response()->json(new DataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Erorr', $datakeluarga), Response::HTTP_INTERNAL_SERVER_ERROR);

    foreach ($datakeluarga as $k) {
      // return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Perubahan berhasil disimpan', $k->data_karyawan_id), Response::HTTP_NOT_FOUND);
      $keluarga = PerubahanKeluarga::create([
        'riwayat_perubahan_id' => $datadiubah->id,
        'data_keluarga_id' => $k['data_keluarga_id'] ?? null, // Akses dengan notasi array
        'nama_keluarga' => $k['nama_keluarga'], // Akses dengan notasi array
        'hubungan' => $k['hubungan']['label'], // Akses dengan notasi array
        'pendidikan_terakhir' => $k['pendidikan_terakhir']['value'], // Akses dengan notasi array
        'status_hidup' => $k['status_hidup']['value'], // Akses dengan notasi array
        'pekerjaan' => $k['pekerjaan'], // Akses dengan notasi array
        'no_hp' => $k['no_hp'], // Akses dengan notasi array
        'email' => $k['email'], // Akses dengan notasi array
      ]);
    }

    return response()->json(new DataResource(Response::HTTP_OK, 'Perubahan berhasil disimpan', $datadiubah), Response::HTTP_OK);
    // return response()->json(new DataResource(Response::HTTP_NOT_FOUND, 'Perubahan berhasil disimpan', $datakeluarga), Response::HTTP_NOT_FOUND);
  }

  public function getdatakaryawandetail()
  {
    $user = User::find(Auth::user()->id);

    if (!$user) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Akun karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    // Get data_karyawan_id from user
    $data_karyawan_id = $user->data_karyawan_id;

    // Find karyawan by data_karyawan_id
    $karyawan = DataKaryawan::where('id',$data_karyawan_id)->first();
    // $karyawan->pendidikan_terakhir = $karyawan->pendidikan_terakhir->label;

    if (!$karyawan) {
      return response()->json([
        'status' => Response::HTTP_NOT_FOUND,
        'message' => 'Data karyawan tidak ditemukan.',
      ], Response::HTTP_NOT_FOUND);
    }

    $role = $karyawan->user->roles->first();

    // $berkasFields = [
    //   'file_ktp' => $karyawan->file_ktp ?? null,
    //   'file_kk' => $karyawan->file_kk ?? null,
    //   'file_sip' => $karyawan->file_sip ?? null,
    //   'file_bpjs_kesehatan' => $karyawan->file_bpjsksh ?? null,
    //   'file_bpjs_ketenagakerjaan' => $karyawan->file_bpjsktk ?? null,
    //   'file_ijazah' => $karyawan->file_ijazah ?? null,
    //   'file_sertifikat' => $karyawan->file_sertifikat ?? null,
    // ];

    // $baseUrl = env('STORAGE_SERVER_DOMAIN');

    // $formattedPaths = [];
    // foreach ($berkasFields as $field => $berkasId) {
    //   $berkas = Berkas::where('id', $berkasId)->first();
    //   if ($berkas) {
    //     $extension = StorageServerHelper::getExtensionFromMimeType($berkas->ext);
    //     // $formattedPaths[$field] = $baseUrl . $berkas->path . '.' . $extension;
    //     $formattedPaths[$field] = $baseUrl . $berkas->path;
    //   } else {
    //     $formattedPaths[$field] = null;
    //   }
    // }

    // Format the karyawan data
    $formattedData = [
      'id' => $karyawan->id,
      'user' => [
        'id' => $karyawan->user->id,
        'nama' => $karyawan->user->nama,
        'email_verified_at' => $karyawan->user->email_verified_at,
        'data_karyawan_id' => $karyawan->user->data_karyawan_id,
        'foto_profil' => $karyawan->user->foto_profil,
        'data_completion_step' => $karyawan->user->data_completion_step,
        'status_aktif' => $karyawan->user->status_aktif,
        'created_at' => $karyawan->user->created_at,
        'updated_at' => $karyawan->user->updated_at
      ],
      'role' => [
        'id' => $role->id,
        'name' => $role->name,
        'deskripsi' => $role->deskripsi,
        'created_at' => $role->created_at,
        'updated_at' => $role->updated_at
      ],
      'potongan_gaji' => DB::table('pengurang_gajis')
        ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
        ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
        ->select(
          'premis.id',
          'premis.nama_premi',
          'premis.kategori_potongan_id',
          'premis.jenis_premi',
          'premis.besaran_premi',
          'premis.minimal_rate',
          'premis.maksimal_rate',
          'premis.created_at',
          'premis.updated_at'
        )
        ->get(),
      'nik' => $karyawan->nik,
      'email' => $karyawan->email,
      'no_rm' => $karyawan->no_rm,
      'no_sip' => $karyawan->no_sip,
      'no_manulife' => $karyawan->no_manulife,
      'tgl_masuk' => $karyawan->tgl_masuk,
      'unit_kerja' => $karyawan->unitkerja,
      'jabatan' => $karyawan->jabatan,
      'kompetensi' => $karyawan->kompetensi,
      'nik_ktp' => $karyawan->nik_ktp,
      'status_karyawan' => $karyawan->statusKaryawan,
      'tempat_lahir' => $karyawan->tempat_lahir,
      'tgl_lahir' => $karyawan->tgl_lahir,
      'kelompok_gaji' => $karyawan->kelompok_gaji,
      'no_rekening' => $karyawan->no_rekening,
      'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
      'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
      'tunjangan_khusus' => $karyawan->tunjangan_khusus,
      'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
      'uang_lembur' => $karyawan->uang_lembur,
      'uang_makan' => $karyawan->uang_makan,
      'ptkp' => $karyawan->ptkp,
      'tgl_keluar' => $karyawan->tgl_keluar,
      'no_kk' => $karyawan->no_kk,
      'alamat' => $karyawan->alamat,
      'gelar_depan' => $karyawan->gelar_depan,
      'no_hp' => $karyawan->no_hp,
      'no_bpjsksh' => $karyawan->no_bpjsksh,
      'no_bpjsktk' => $karyawan->no_bpjsktk,
      'tgl_diangkat' => $karyawan->tgl_diangkat,
      'masa_kerja' => $karyawan->masa_kerja,
      'npwp' => $karyawan->npwp,
      'jenis_kelamin' => $karyawan->jenis_kelamin,
      'agama' => $karyawan->kategoriagama,
      'golongan_darah' => $karyawan->golonganDarah,
      'pendidikan_terakhir' => $karyawan->pendidikan_terakhir,
      'tinggi_badan' => $karyawan->tinggi_badan,
      'berat_badan' => $karyawan->berat_badan,
      'no_ijazah' => $karyawan->no_ijazah,
      'tahun_lulus' => $karyawan->tahun_lulus,
      'no_str' => $karyawan->no_str,
      'asal_sekolah' => $karyawan->asal_sekolah,
      'gelar_belakang' => $karyawan->gelar_belakang,
      'masa_berlaku_str' => $karyawan->masa_berlaku_str,
      'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
      'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
      'masa_diklat' => $karyawan->masa_diklat,
      'created_at' => $karyawan->created_at,
      'updated_at' => $karyawan->updated_at
    ];

    return response()->json([
      'status' => Response::HTTP_OK,
      'message' => "Detail karyawan '{$karyawan->user->nama}' berhasil ditampilkan.",
      'data' => $formattedData,
    ], Response::HTTP_OK);
  }
}
