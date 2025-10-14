<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\Teacher;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = AcademicLevel::all();
        $teachers = Teacher::all();

        foreach ($levels as $level) {
            // Define section names (A and B only)
            $sectionNames = ['A', 'B'];

            foreach ($sectionNames as $index => $sectionName) {
                // Assign deterministic teacher as section head based on index
                $sectionHeadTeacher = $teachers->get($index % $teachers->count());

                Section::firstOrCreate(
                    [
                        'level_id' => $level->id,
                        'name' => $sectionName,
                    ],
                    [
                        'status' => 'active',
                        'section_head_teacher_id' => $sectionHeadTeacher->id,
                    ]
                );
            }
        }
    }
}
