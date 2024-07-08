<?php
// app/Helpers/LocationHelper.php
namespace App\Helpers;

use App\Models\User;

class UserActiveHelper
{
    public static function checkActive(User $user)
    {
        if ($user->status_akun == 1) {
            return true;
        } else {
            return false;
        }
    }
}
