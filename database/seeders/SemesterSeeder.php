<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
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
            \App\Models\Semester::create([
                'academic_year_id' => $academicYear->id,
                'name' => \App\Enums\SemesterName::FirstSemester->value,
                'start_date' => '2024-08-01',
                'end_date' => '2024-12-31',
                'status' => 'active',
            ]);

            \App\Models\Semester::create([
                'academic_year_id' => $academicYear->id,
                'name' => \App\Enums\SemesterName::SecondSemester->value,
                'start_date' => '2025-01-01',
                'end_date' => '2025-05-31',
                'status' => 'active',
            ]);
        }
    }
}
