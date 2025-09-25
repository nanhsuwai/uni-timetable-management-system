<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;

class AcademicProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'program_id');
    }
}
