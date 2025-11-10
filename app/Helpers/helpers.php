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
        return AcademicYear::getActiveYears();
    }
}

if (! function_exists('_getActiveAcademicYearId')) {
    function _getActiveAcademicYearId() {
        $activeYear = _getActiveAcademicYear();
        return $activeYear ? $activeYear->id : null;
    }
}

if (! function_exists('_generateTeacherCode')) {
    function _generateTeacherCode() {
        $year = date('Y');
        $prefix = 'T' . $year;

        // Find the last teacher code for this year
        $lastTeacher = \App\Models\Teacher::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastTeacher) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastTeacher->code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format with leading zeros (e.g., T2025001)
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
