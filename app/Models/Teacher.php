<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\DepartmentOption;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'department',
        'head_of_department',
    ];

    /**
     * Cast fields to appropriate types
     */
    protected $casts = [
        'head_of_department' => 'boolean',
        // 'department' => DepartmentOption::class, // Use enum casting
    ];

    /**
     * Get the subjects assigned to this teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }

    /**
     * Get the sections this teacher heads.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'section_head_teacher_id');
    }

    /**
     * Get the timetable entries assigned to this teacher.
     */
    public function timetableEntries()
    {
        return $this->belongsToMany(TimetableEntry::class, 'timetable_entry_teacher');
    }

    /**
     * Helper to get department display name
     */
    public function getDepartmentName(): string
    {
        return $this->department->value; // returns the enum string value
    }

    /**
     * Helper to check if teacher is head of department
     */
    public function isHeadOfDepartment(): bool
    {
        return $this->head_of_department;
    }
}
