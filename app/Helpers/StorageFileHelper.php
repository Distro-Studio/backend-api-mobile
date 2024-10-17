<?php
namespace App\Helpers;

use App\Models\Berkas;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StorageFileHelper
{
    public static function uploadToServer(Request $request, $filename='File Upload', $filerequestname)
    {
        $response = Http::asForm()->post(env('URL_STORAGE').'/api/login',[
            'username' => env('USERNAME_STORAGE'),
            'password' => env('PASSWORD_STORAGE')
        ]);
        $logininfo = $response->json();
        $token = $logininfo['data']['token'];
        $file = $request->file($filerequestname);

        $responseupload = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->asMultipart()->post(env('URL_STORAGE').'/api/upload',[
            'filename' => $filename,
            'file' => fopen($file->getRealPath(), 'r'),
            'kategori' => 'Umum'
        ]);

        $uploadinfo = $responseupload->json();
        $dataupload = $uploadinfo['data'];

        $logout = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('URL_STORAGE').'/api/logout');

        return $dataupload;
    }

    public static function checkfile(Berkas $berkas)
    {
        $response = Http::asForm()->post(env('URL_STORAGE').'/api/login',[
            'username' => env('USERNAME_STORAGE'),
            'password' => env('PASSWORD_STORAGE')
        ]);
        $logininfo = $response->json();
        $token = $logininfo['data']['token'];

        $responseupload = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->asForm()->post(env('URL_STORAGE').'/api/get-file',[
            'id_file' => $berkas->file_id,
        ]);


        $logout = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('URL_STORAGE').'/api/logout');


    }

    public static function downloadFromServer(Berkas $berkas){
        // dd($berkas->ext);
        // $berkasfile = Berkas::where('id', 5)->first();
        $ext = explode('/', $berkas->ext);
        $response = Http::asForm()->post(env('URL_STORAGE').'/api/login',[
            'username' => env('USERNAME_STORAGE'),
            'password' => env('PASSWORD_STORAGE')
        ]);
        $logininfo = $response->json();
        $token = $logininfo['data']['token'];

        $responseupload = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->asForm()->post(env('URL_STORAGE').'/api/get-file',[
            'id_file' => $berkas->file_id,
        ]);

        // $uploadinfo = $responseupload->json();
        // $dataupload = $uploadinfo['data'];

        $logout = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('URL_STORAGE').'/api/logout');

        if ($responseupload->successful()) {
            // Mengambil nama file dari header atau set default
            $fileName = $responseupload->header('Content-Disposition')
            ? self::getFileNameFromHeader($responseupload->header('Content-Disposition'))
            : $berkas->nama_file . '.' . self::getExtensionFromMimeType($responseupload->header('Content-Type'));

            // Mengambil konten file dari respon
            $fileContent = $responseupload->body();
            $data = [];
            $data['data'] = $fileContent;
            // $data['ext'] = $ext[1];
            $data['filename'] = $fileName;
            // Mengirimkan file ke pengguna
            return $data;
            // return response($fileContent, 200)
            //     ->header('Content-Type', $responseupload->header('Content-Type'))
            //     ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }
    }

    public static function deleteFromServer(Berkas $berkas) {
        $response = Http::asForm()->post(env('URL_STORAGE').'/api/login',[
            'username' => env('USERNAME_STORAGE'),
            'password' => env('PASSWORD_STORAGE')
        ]);
        $logininfo = $response->json();
        $token = $logininfo['data']['token'];

        $responseupload = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->asMultipart()->post(env('URL_STORAGE').'/api/delete-file',[
            'file_id' => $berkas->file_id,
        ]);

        $uploadinfo = $responseupload->json();
        $dataupload = $uploadinfo['data'];

        $logout = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('URL_STORAGE').'/api/logout');

        return $dataupload;
    }

    private static function getFileNameFromHeader($header)
    {
        // Extract filename from Content-Disposition header
        if (preg_match('/filename="(.+)"/', $header, $matches)) {
            return $matches[1];
        }
        return 'downloaded_file';
    }

    public static function getExtensionFromMimeType($mimeType)
    {
        $mimeMap = [
            // Text files
            'text/plain' => 'txt',
            'text/html' => 'html',
            'text/css' => 'css',
            'text/csv' => 'csv',
            'text/xml' => 'xml',

            // Image files
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',

            // Audio files
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'audio/x-ms-wma' => 'wma',

            // Video files
            'video/mp4' => 'mp4',
            'video/ogg' => 'ogv',
            'video/webm' => 'webm',
            'video/x-msvideo' => 'avi',
            'video/x-ms-wmv' => 'wmv',

            // Application files
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/json' => 'json',
            'application/javascript' => 'js',
            'application/vnd.oasis.opendocument.text' => 'odt',
            'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
            'application/vnd.oasis.opendocument.presentation' => 'odp',

            // Font files
            'font/otf' => 'otf',
            'font/ttf' => 'ttf',
            'font/woff' => 'woff',
            'font/woff2' => 'woff2',

            // Binary and others
            'application/octet-stream' => 'bin',
        ];

        return $mimeMap[$mimeType] ?? 'bin';
    }
}
