<?php

namespace App\Models;
use App\Enums\LevelName;
use App\Enums\ProgramOption; // Still needed if you use it elsewhere
use App\Enums\SemesterName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
        'level',
       
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
    public function scopeCSTSubject($query)
    {
        return $query->where(function($q) {
            // Include subjects that start with CST (3 characters)
            $q->whereRaw("SUBSTRING(code, 1, 3) = 'CST'")
              // OR subjects that don't start with CS or CT (2 characters)
              ->orWhereRaw("SUBSTRING(code, 1, 2) NOT IN ('CS', 'CT')");
        });
    }
}