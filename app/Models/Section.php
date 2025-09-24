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
    ];

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
