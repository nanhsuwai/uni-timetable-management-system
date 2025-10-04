<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'program_id',
        'level_id',
        'section_id',
        'classroom_id',
        'subject_id',
        'time_slot_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

   /*  public function teacher()
    {
        return $this->belongsToMany(Teacher::class, 'timetable_entry_teacher');
    } */

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'timetable_entry_teacher');
    }

    /**
     * Get the time slot that owns the timetable entry
     */
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
