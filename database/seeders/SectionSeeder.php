<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = \App\Models\AcademicLevel::all();
        $teachers = \App\Models\Teacher::all();

        foreach ($levels as $level) {
            // Default Section A for all levels
            \App\Models\Section::firstOrCreate([
                'level_id' => $level->id,
                'name' => 'A',
            ], [
                'status' => 'active',
                'section_head_teacher_id' => $teachers->random()->id,
            ]);

            // Sections B and C for SecondYear, ThirdYear, FourthYear
            if (in_array($level->name, ['Second Year', 'Third Year', 'Fourth Year'])) {
                $additionalSections = ['B', 'C'];
                foreach ($additionalSections as $sectionName) {
                    \App\Models\Section::firstOrCreate([
                        'level_id' => $level->id,
                        'name' => $sectionName,
                    ], [
                        'status' => 'active',
                        'section_head_teacher_id' => $teachers->random()->id,
                    ]);
                }
            }
        }
    }
}
