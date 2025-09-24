<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\AcademicYear;

if (! function_exists('_createImageFromBase64Code')) {
    function _createImageFromBase64Code($string) {
        if(imagecreatefromstring($string) == false) {
            return false;
        }
        return true;
    }
}

if (! function_exists('_YmdFormat')) {
    function _YmdFormat($date) {
        return Carbon::parse($date)->format('Y-m-d');
    }
}

if (! function_exists('_dMYFormat')) {
    function _dMYFormat($date) {
        return Carbon::parse($date)->format('d-m-Y');
    }
}

if (! function_exists('_timeFormat')) {
    function _timeFormat($time) {
        return Carbon::parse($time)->format('h:i A');
    }
}

if (! function_exists('_randomElements')) {
    function _randomElements($data) {
        return Arr::random($data);
    }
}

if (! function_exists('_getActiveAcademicYear')) {
    function _getActiveAcademicYear() {
        return AcademicYear::getActive();
    }
}

if (! function_exists('_getActiveAcademicYearId')) {
    function _getActiveAcademicYearId() {
        $activeYear = _getActiveAcademicYear();
        return $activeYear ? $activeYear->id : null;
    }
}

