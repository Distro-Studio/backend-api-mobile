<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

}
