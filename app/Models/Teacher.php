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
    ];

    /**
     * Get the subjects assigned to this teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }
}
