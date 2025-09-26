<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'status',
        'department',
        'head_of_department',
    ];

    protected $casts = [
        'head_of_department' => 'boolean',
    ];

    /**
     * Get the subjects assigned to this teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'section_head_teacher_id');
    }
}
