<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $academicYear = \App\Models\AcademicYear::where('name', '2024-2025')->first();

        if ($academicYear) {
            // Create academic programs
            $programs = [
                [
                    'name' => 'CST',
                    'program_type' => 'CST',
                    'levels' => [\App\Enums\LevelName::FirstYear->value]
                ],
                [
                    'name' => 'Computer Technology',
                    'program_type' => 'CT',
                    'levels' => [
                        \App\Enums\LevelName::SecondYear->value,
                        \App\Enums\LevelName::ThirdYear->value,
                        \App\Enums\LevelName::FourthYear->value,
                        \App\Enums\LevelName::FifthYear->value,

                    ]
                ],
                [
                    'name' => 'Computer Science',
                    'program_type' => 'CS',
                    'levels' => [
                        \App\Enums\LevelName::SecondYear->value,
                        \App\Enums\LevelName::ThirdYear->value,
                        \App\Enums\LevelName::FourthYear->value,
                        \App\Enums\LevelName::FifthYear->value,

                    ]
                ],
               /*  [
                    'name' => 'Diploma',
                    'program_type' => 'Diploma',
                    'levels' => [
                        \App\Enums\LevelName::Diploma->value, 
                    ]
                ], */
               
            ];

            foreach ($programs as $programData) {
                $program = \App\Models\AcademicProgram::firstOrCreate([
                    'academic_year_id' => $academicYear->id,
                    'name' => $programData['name'],
                ], [
                    'program_type' => $programData['program_type'],
                    'status' => 'active',
                ]);

                // Create academic levels for each program
                foreach ($programData['levels'] as $levelName) {
                    \App\Models\AcademicLevel::firstOrCreate([
                        'program_id' => $program->id,
                        'name' => $levelName,
                    ], [
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
