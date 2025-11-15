<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Enums\LevelName;

class AcademicLevelSeeder extends Seeder
{
    public function run(): void
    {
        $programs = AcademicProgram::all();

        $activeYear = AcademicYear::getActiveYears()->first();


        foreach ($programs as $program) {

            // Helper function (now receives $activeYear)
            $createYear = function ($programId, $levelName) use ($activeYear) {
                AcademicLevel::create([
                    'academic_year_id' => $activeYear->id,
                    'program_id'        => $programId,
                    'name'              => $levelName,
                    'semester'          => 'First Semester',
                ]);

                AcademicLevel::create([
                    'academic_year_id' => $activeYear->id,
                    'program_id'        => $programId,
                    'name'              => $levelName,
                    'semester'          => 'Second Semester',
                ]);
            };

            // CST → only First Year
            if ($program->program_type === 'CST') {
                $createYear($program->id, LevelName::FirstYear->value);
            }

            // CT / CS → Second–Fourth Year
            elseif (in_array($program->program_type, ['CT', 'CS'])) {
                $years = [
                    LevelName::SecondYear->value,
                    LevelName::ThirdYear->value,
                    LevelName::FourthYear->value,
                ];

                foreach ($years as $year) {
                    $createYear($program->id, $year);
                }
            }

            // Diploma → 1 year
            elseif ($program->program_type === 'Diploma') {
                $createYear($program->id, LevelName::Diploma->value);
            }
        }
    }
}
