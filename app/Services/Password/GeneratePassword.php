<?php

namespace App\Services\Password;

class GeneratePassword
{
    public static function generatePassword($length = 12) : string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&';
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    public static function randomOTP($length) : string
    {
        $chars = '123456789';
        $otp = substr(str_shuffle($chars), 0, $length);
        return $otp;
    }
}
