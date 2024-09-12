<?php

namespace App\Http\Controllers;

use App\Helpers\StorageFileHelper;
use App\Models\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CobaController extends Controller
{
    public function compareFaces(Request $request)
    {
        // $checkInImagePath = $request->input('check_in_image_path');
        // $checkOutImagePath = $request->input('check_out_image_path');
        $checkInImagePath = public_path('/face-recognition/face1.jpg');
        $checkOutImagePath = public_path('/face-recognition/face3.jpg');

        $command = escapeshellcmd("python3 ". public_path('/face-recognition/compare_faces.py') ." {$checkInImagePath} {$checkOutImagePath}");
        // $output = shell_exec($command);

        // if (trim($output) == "True") {
        //     return response()->json(['status' => 'success', 'message' => 'Faces match']);
        // } else {
        //     return response()->json(['status' => 'fail', 'message' => 'Faces do not match']);
        // }

        // Log::info("Running command: $command");

        $output = shell_exec($command . " 2>&1"); // Tangkap error juga
        // Log::info("Command output: $output");

        if ($output === null) {
            return response()->json(['status' => 'fail', 'message' => 'Command failed to execute.']);
        }

        if (trim($output) == "match") {
            return response()->json(['status' => 'success', 'message' => 'Faces match!']);
        } else {
            return response()->json(['status' => 'fail', 'message' => 'Faces do not match.']);
        }

        // return response()->json(['status' => 'success', 'message' => shell_exec($command)]);
    }

    public function cobadownload()
    {
        $berkasfile = Berkas::where('id', 5)->first();
        $fileContent = StorageFileHelper::downloadFromServer($berkasfile);
        // dd($fileContent);
        return response($fileContent['data'], 200)
                ->header('Content-Type', $berkasfile->ext)
                ->header('Content-Disposition', 'attachment; filename="' . $fileContent['filename'] . '"');
        // $ext = explode('/', $berkasfile->ext);
        // $response = Http::asForm()->post('http://127.0.0.1:8001/api/login',[
        //     'username' => env('USERNAME_STORAGE'),
        //     'password' => env('PASSWORD_STORAGE')
        // ]);
        // $logininfo = $response->json();
        // $token = $logininfo['data']['token'];

        // $responseupload = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $token,
        // ])->asForm()->post('http://127.0.0.1:8001/api/get-file',[
        //     'id_file' => $berkasfile->file_id,
        // ]);

        // // $uploadinfo = $responseupload->json();
        // // $dataupload = $uploadinfo['data'];

        // $logout = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $token,
        // ])->post('http://127.0.0.1:8001/api/logout');

        // if ($responseupload->successful()) {
        //     // Mengambil nama file dari header atau set default
        //     $fileName = $responseupload->header('Content-Disposition')
        //     ? $this->getFileNameFromHeader($responseupload->header('Content-Disposition'))
        //     : $berkasfile->nama_file . '.' . $this->getExtensionFromMimeType($responseupload->header('Content-Type'));

        //     // Mengambil konten file dari respon
        //     $fileContent = $responseupload->body();

        //     // Mengirimkan file ke pengguna
        //     return response($fileContent, 200)
        //         ->header('Content-Type', $responseupload->header('Content-Type'))
        //         ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        // }
    }

    // private function getFileNameFromHeader($header)
    // {
    //     // Extract filename from Content-Disposition header
    //     if (preg_match('/filename="(.+)"/', $header, $matches)) {
    //         return $matches[1];
    //     }
    //     return 'downloaded_file';
    // }

    // private function getExtensionFromMimeType($mimeType)
    // {
    //     $mimeMap = [
    //         // Text files
    //         'text/plain' => 'txt',
    //         'text/html' => 'html',
    //         'text/css' => 'css',
    //         'text/csv' => 'csv',
    //         'text/xml' => 'xml',

    //         // Image files
    //         'image/jpeg' => 'jpg',
    //         'image/png' => 'png',
    //         'image/gif' => 'gif',
    //         'image/bmp' => 'bmp',
    //         'image/webp' => 'webp',
    //         'image/svg+xml' => 'svg',

    //         // Audio files
    //         'audio/mpeg' => 'mp3',
    //         'audio/ogg' => 'ogg',
    //         'audio/wav' => 'wav',
    //         'audio/x-ms-wma' => 'wma',

    //         // Video files
    //         'video/mp4' => 'mp4',
    //         'video/ogg' => 'ogv',
    //         'video/webm' => 'webm',
    //         'video/x-msvideo' => 'avi',
    //         'video/x-ms-wmv' => 'wmv',

    //         // Application files
    //         'application/pdf' => 'pdf',
    //         'application/zip' => 'zip',
    //         'application/x-rar-compressed' => 'rar',
    //         'application/vnd.ms-excel' => 'xls',
    //         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    //         'application/msword' => 'doc',
    //         'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    //         'application/vnd.ms-powerpoint' => 'ppt',
    //         'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    //         'application/json' => 'json',
    //         'application/javascript' => 'js',
    //         'application/vnd.oasis.opendocument.text' => 'odt',
    //         'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
    //         'application/vnd.oasis.opendocument.presentation' => 'odp',

    //         // Font files
    //         'font/otf' => 'otf',
    //         'font/ttf' => 'ttf',
    //         'font/woff' => 'woff',
    //         'font/woff2' => 'woff2',

    //         // Binary and others
    //         'application/octet-stream' => 'bin',
    //     ];

    //     return $mimeMap[$mimeType] ?? 'bin';
    // }

}
