<?php
// app/Helpers/LocationHelper.php
namespace App\Helpers;

use App\Models\DataKaryawan;
use App\Models\User;

class DataCompletionHelper
{
    public static function checkCompletion(User $user)
    {
        // $datakaryawan = DataKaryawan::where('user_id', $user->id)->first();
        if ($user->data_completion_step == 1) {
            return true;
        } else {
            return false;
        }
    }
}
