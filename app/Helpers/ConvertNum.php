<?php

namespace App\Helpers;

class ConvertNum
{
    public static $EngToMmNum = [
        '0' => '၀',
        '1' => '၁',
        '2' => '၂',
        '3' => '၃',
        '4' => '၄',
        '5' => '၅',
        '6' => '၆',
        '7' => '၇',
        '8' => '၈',
        '9' => '၉',
    ];

    public static $MmToEng = [
        '၀' => '0',
        '၁' => '1',
        '၂' => '2',
        '၃' => '3',
        '၄' => '4',
        '၅' => '5',
        '၆' => '6',
        '၇' => '7',
        '၈' => '8',
        '၉' => '9',
    ];

    public static function mm($data)
    {
        return strtr($data, self::$EngToMmNum);
    }

    public static function en($data)
    {
        return strtr($data, self::$MmToEng);
    }
}

