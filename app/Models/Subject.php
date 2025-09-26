<?php

namespace App\Models;
use App\Enums\LevelName;
use App\Enums\ProgramOption;
use App\Enums\SemesterName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
        'level',
        'program',
        'semester',
    ];
    protected $casts = [
        'level' => LevelName::class,
        'program' => ProgramOption::class,
        'semester' => SemesterName::class,
    ];

    /**
     * Get the teachers assigned to this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
}
