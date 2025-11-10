<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AcademicYear extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

   
    protected $casts = [
        
        'status' => 'string', 
    ];

   
    public function getStatusNumericAttribute()
    {
       
        return $this->status === 'active' ? 1 : 0;
    }

    
    
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public static function getActiveYears()
    {
        return static::active()->get();
    }
    
   
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // --- Relationships ---

    public function academicPrograms()
    {
        return $this->hasMany(AcademicProgram::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}