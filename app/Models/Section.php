<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicLevel;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'name',
        'status',
        'section_head_teacher_id',
    ];

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
    public function classroom()
    {
        return $this->hasOne(Classroom::class, 'section_id');
    }

    public function sectionHeadTeacher()
    {
        return $this->belongsTo(Teacher::class, 'section_head_teacher_id');
    }
}
