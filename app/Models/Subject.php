<?php

namespace App\Models;
use App\Enums\LevelName;
use App\Enums\ProgramOption; // Still needed if you use it elsewhere
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
        'program', // This is now a simple string containing CSV
        'semester',
    ];
    
    // 🛑 REMOVE 'program' CAST
    protected $casts = [
        'level' => LevelName::class,
        'semester' => SemesterName::class,
        // 'program' => ProgramOption::class, // <-- REMOVED!
    ];

    /**
     * Get the teachers assigned to this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
}