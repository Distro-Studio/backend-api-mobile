<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Http\Resources\DataResource;
use App\Http\Resources\WithoutDataResource;
use App\Models\Berkas;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

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
      'nik_ktp' => 'required|integer|digits:16',
      'no_kk' => 'required|integer|digits:16',
      'agama' => 'required',
      'golongan_darah' => 'required',
      'tinggi_badan' => 'required|integer',
      'alamat' => 'required',
      'tahun_lulus' => 'required|numeric',
      'no_ijazah' => 'required',
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
      'nik_ktp.integer' => 'Nomor Induk Kependudukan harus berupa angka',
      'nik_ktp.digits' => 'Nomor Induk Kependudukan harus terdiri dari 16 digit',
      'no_kk.required' => 'Nomor Kartu Keluarga harus di isi',
      'no_kk.integer' => 'Nomor Kartu Keluarga harus berupa angka',
      'no_kk.digits' => 'Nomor Kartu Keluarga harus terdiri dari 16 digit',
      'agama.required' => 'Agama harus diisi',
      'golongan_darah.required' => 'Golongan darah harus diisi',
      'tinggi_badan.required' => 'Tinggi badan harus diisi',
      'tinggi_badan.integer' => 'Tinggi badan harus berupa angka',
      'alamat.required' => 'Alamat harus diisi',
      'tahun_lulus.required' => 'Tahun lulus harus diisi',
      'tahun_lulus.numeric' => 'Tahun lulus harus berupa angka',
      'no_ijazah.required' => 'Nomor ijazah harus diisi',
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
      $data->alamat = $request->alamat;
      $data->no_ijazah = $request->no_ijazah;
      $data->tahun_lulus = $request->tahun_lulus;
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
        $validator = Validator::make($request->all(),[
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
                    'nama_keluarga' => $k['nama'],
                    'hubungan' => $k['hubungan_keluarga']['value'],
                    'pendidikan_terakhir' => 'ini pendidikan',
                    'status_hidup' => $k['status_hidup']['value'],
                    'pekerjaan' => $k['pekerjaan'],
                    'no_hp' => $k['telepon'],
                    'email' => $k['email'],
                ]);
                // return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $k), Response::HTTP_OK);

            }

            $user = User::where('id', Auth::user()->id)->update(['data_completion_step' => 3]);

            return response()->json(new DataResource(Response::HTTP_OK, 'Data berhasil disimpan', $keluarga), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }

  }

  public function getkeluarga()
  {
    try {
      //code...
      $karyawan = DataKaryawan::where('user_id', Auth::user()->id)->first();
      $data = DataKeluarga::where('data_karyawan_id', $karyawan->id)->get();
      if ($data->isEmpty()) {
        return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
      }

      return response()->json(new DataResource(Response::HTTP_OK, 'Data keluarga ' . Auth::user()->name, $data), Response::HTTP_OK);
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
      'no_str' => 'required',
      'masa_berlaku_str' => 'required',
      'no_sip' => 'required',
      'masa_berlaku_sip' => 'required',
      'no_bpjsksh' => 'required',
      'no_bpjsktk' => 'required'
    ], [
      'no_str.required' => 'Nomor STR harus diisi',
      'masa_berlaku_str.required' => 'Masa berlaku STR harus diisi',
      'no_sip.required' => 'Nomor SIP harus diisi',
      'masa_berlaku_sip.required' => 'Masa berlaku SIP harus diisi',
      'no_bpjsksh.required' => 'Nomor BPJS Kesehatan harus diisi',
      'no_bpjsktk.required' => 'Nomor BPJS Ketenagakerjaan harus diisi'
    ]);

    if ($validator->fails()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()), Response::HTTP_NOT_ACCEPTABLE);
    }

    $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

    if (!$data) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'), Response::HTTP_NOT_FOUND);
    }

    $data->no_str = $request->no_str;
    $data->masa_berlaku_str = $request->masa_berlaku_str;
    $data->no_sip = $request->no_sip;
    $data->masa_berlaku_sip = $request->masa_berlaku_sip;
    $data->no_bpjsksh = $request->no_bpjsksh;
    $data->no_bpjsktk = $request->no_bpjsktk;
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
        //KTP
        $uploadktp = StorageFileHelper::uploadToServer($request, 'KTP', 'ktp');

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
        $uploadkk = StorageFileHelper::uploadToServer($request, 'KK', 'kk');

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
        $uploadsip = StorageFileHelper::uploadToServer($request, 'SIP', 'sip');
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
        $uploadbpjs = StorageFileHelper::uploadToServer($request, 'BPJS Kesehatan', 'bpjs_kesehatan');
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
        $uploadbpjstk = StorageFileHelper::uploadToServer($request, 'BPJS Ketenagakerjaan', 'bpjs_ketenagakerjaan');
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
        $uploadijazah = StorageFileHelper::uploadToServer($request, 'Ijazah', 'ijazah');
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
        $uploadsertifikat = StorageFileHelper::uploadToServer($request, 'Sertifikat', 'sertifikat');
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
}
