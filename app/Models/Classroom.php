<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\AcademicLevel;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'section_id',
        'room_no',
        'status',
        'is_available',
    ];

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
