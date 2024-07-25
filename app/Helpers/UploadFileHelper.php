<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UploadFileHelper
{
    public static function uploadToServer(Request $request, $filename='File Upload')
    {
        $response = Http::asForm()->post('http://127.0.0.1:8001/api/login',[
            'username' => env('USERNAME_STORAGE'),
            'password' => env('PASSWORD_STORAGE')
        ]);
        $logininfo = $response->json();
        $token = $logininfo['data']['token'];
        $file = $request->file('foto');

        $responseupload = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->asMultipart()->post('http://127.0.0.1:8001/api/upload',[
            'filename' => $filename,
            'file' => fopen($file->getRealPath(), 'r'),
            'kategori' => 'Umum'
        ]);

        $uploadinfo = $responseupload->json();
        $dataupload = $uploadinfo['data'];

        $logout = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('http://127.0.0.1:8001/api/logout');

        return $dataupload;
    }
}
