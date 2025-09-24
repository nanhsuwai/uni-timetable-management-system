<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicProgram;

class AcademicLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'status',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }
}
