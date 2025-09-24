<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'code',
        'name',
        'status',
    ];

    /**
     * Get the academic year that owns the subject.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester that owns the subject.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the teachers assigned to this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
}
