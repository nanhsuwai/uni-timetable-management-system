<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Enums\LevelName;

class AcademicLevelSeeder extends Seeder
{
    public function run(): void
    {
        $programs = AcademicProgram::all();

        foreach ($programs as $program) {
            if($program->program_type === 'CST') {
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::FirstYear->value,
                ]);
            } elseif (in_array($program->program_type, ['CT', 'CS'])) {
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::SecondYear->value,
                ]);
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::ThirdYear->value,
                ]);
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::FourthYear->value,
                ]);
            } /* elseif ($program->program_type === 'Master') {
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::Coursework->value,
                ]);
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::Thesis->value,
                ]); 
            }*/ elseif ($program->program_type === 'Diploma') {
                AcademicLevel::create([
                    'program_id' => $program->id,
                    'name' => LevelName::Diploma->value,
                ]);
            }
            // Add more conditions for other program types if needed
        }
    }
}
