<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Section;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'room_no',
        'status',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
