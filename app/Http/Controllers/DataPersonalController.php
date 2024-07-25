<?php

namespace App\Http\Controllers;

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
        $validator = Validator::make($request->all(),[
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
        ],[
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

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            $user = User::where('id', Auth::user()->id)->first();
            $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

            if(!$user)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND,'Akun user tidak ditemukan'),Response::HTTP_NOT_FOUND);
            }

            if(!$data)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'),Response::HTTP_NOT_FOUND);
            }

            $nama = null;
            if($request->nama)
            {
                $user->name = $request->nama;
                $user->save();
            }

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
            $data->tahun_lulus = $request->tahun_lulus;
            $data->save();

            return response()->json(new DataResource(Response::HTTP_OK,'Data berhasil disimpan', $request->all()),Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, $e),Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function storekeluarga(Request $request)
    {
        $validator = Validator::make($request->all(),[
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

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

            if(!$data)
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'),Response::HTTP_NOT_FOUND);
            }

            $keluarga = DataKeluarga::create([
                'data_karyawan_id' => $data->id,
                'nama_keluarga' => $request->nama_keluarga,
                'hubungan' => $request->hubungan,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'status_hidup' => $request->status_hidup,
                'pekerjaan' => $request->pekerjaan,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
            ]);

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
            if($data->isEmpty())
            {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'),Response::HTTP_NOT_FOUND);
            }

            return response()->json(new DataResource(Response::HTTP_OK, 'Data keluarga '.Auth::user()->name, $data), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatekeluarga(DataKeluarga $dataKeluarga, Request $request)
    {
        $validator = Validator::make($request->all(),[
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

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
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
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something wrong'),Response::HTTP_INTERNAL_SERVER_ERROR);
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

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
        }

        $data = DataKaryawan::where('user_id', Auth::user()->id)->first();

        if(!$data)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'),Response::HTTP_NOT_FOUND);
        }

        $data->no_str = $request->no_str;
        $data->masa_berlaku_str = $request->masa_berlaku_str;
        $data->no_sip = $request->no_sip;
        $data->masa_berlaku_sip = $request->masa_berlaku_sip;
        $data->no_bpjsksh = $request->no_bpjsksh;
        $data->no_bpjsktk = $request->no_bpjsktk;
        $data->save();

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
        $validator = Validator::make($request->all(),[
            'nama' => 'required',
            // 'kategori' => 'in:Pribadi,Umum',
            'file' => 'required|file',
        ], [
            'nama.required' => 'Judul harus diisi',
            // 'kategori.in' => 'Kategori harus berisi Pribadi atau Umum',
            'file.required' => 'File harus diisi',
            'file.file' => 'File harus berupa file'
        ]);

        if ($validator->fails())
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, $validator->errors()),Response::HTTP_NOT_ACCEPTABLE);
        }

        $file = $request->file('file');
        $filename = $file->hashName();
        $size = $this->formatBytes($file->getSize());
        $file->move(public_path('storage/file'), $filename);

        $berkas = Berkas::create([
            'user_id' => Auth::user()->id,
            'nama' => $request->nama,
            'kategori' => 'Pribadi',
            'path' => Storage::url('file/' . $filename),
            'tgl_upload' => date('Y-m-d H:m:s'),
            'nama_file' => $filename,
            'ext' => $file->getExtension(),
            'size' => $size,
        ]);

        if(!$berkas)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Berkas gagal di upload'),Response::HTTP_BAD_REQUEST);
        }

        return response()->json(new DataResource(Response::HTTP_OK, 'Berkas berhasil di upload', $berkas), Response::HTTP_OK);
    }

    public function checkuseractive()
    {
        $user = User::where('id', Auth::user()->id)->first();

        if(!$user)
        {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data user tidak ditemukan'),Response::HTTP_NOT_FOUND);
        }

        if($user->data_completion_step == 0)
        {
            return response()->json(new DataResource(Response::HTTP_FORBIDDEN, 'Akun anda sedang tidak aktif', ['data_completion_step' => false]),Response::HTTP_FORBIDDEN);
        }

        if($user->data_completion_step == 1)
        {
            return response()->json(new DataResource(Response::HTTP_OK, 'Akun anda sedang tidak aktif', ['data_completion_step' => true]),Response::HTTP_FORBIDDEN);
        }
    }
}
