<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run() : void
    {
        \App\Models\User::factory()->create();
      
      

        $this->call(
            [
                PermissionSeeder::class,
                AcademicYearSeeder::class,
                AcademicProgramSeeder::class,
                TeacherSeeder::class,
                SectionSeeder::class,
                ClassroomSeeder::class,
                SubjectSeeder::class,
                SemesterSeeder::class,
            ]
        );
    }
}
