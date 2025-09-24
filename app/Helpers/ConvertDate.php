<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Helpers\ConvertNum;

class ConvertDate
{
    public static $noInEngToMonth = [
        '1' => 'ဇန်နဝါရီလ',
        '2' => 'ဖေဖေါ်ဝါရီလ',
        '3' => 'မတ်လ',
        '4' => 'ဧပြီလ',
        '5' => 'မေလ',
        '6' => 'ဇွန်လ',
        '7' => 'ဇူလိုင်လ',
        '8' => 'သြဂုတ်လ',
        '9' => 'စက်တင်ဘာလ',
        '10' => 'အောက်တိုဘာလ',
        '11' => 'နိုဝင်ဘာလ',
        '12' => 'ဒီဇင်ဘာလ',
    ];

    public static $noInEngToMonthEng = [
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    ];

    public static function mmDate($date)
    {
        $y = Carbon::parse( $date )->format('Y');
        $m = Carbon::parse( $date )->format('n');
        $d = Carbon::parse( $date )->format('j');
        return ConvertNum::mm($y).' ခုနှစ်၊ '.strtr($m, self::$noInEngToMonth).' ('.ConvertNum::mm($d).') ရက် ';
    }

    public static function enDate( $date )
    {
        return date_format(new DateTime($date), "jS F, Y");
    }
}
