<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Enums\DepartmentOption;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // List of teachers
        $teachers = [
            [
                'code' => 'T001', 
                'name' => 'Daw Win Theigi Myint', 
                'email' => 'wintheigimyint@ucsh.edu.mm', 
                'phone' => '+959123456789', 
                'status' => 'active', 
                'department' => DepartmentOption::FCS, 
                'head_of_department' => true
            ],
            [
                'code' => 'T002', 
                'name' => 'Dr. Khin Theint Theint Soe', 
                'email' => 'khintheinttheintsoe@ucsh.edu.mm', 
                'phone' => '+959123456790', 
                'status' => 'active', 
                'department' => DepartmentOption::FCS, 
                'head_of_department' => false
            ],
            [
                'code' => 'T004',
                'name' => 'Dr. Thidar Win',
                'email' => 'thidarwin@ucsh.edu.mm',
                'phone' => '+959123456792',
                'status' => 'active',
                'department' => DepartmentOption::FCST,
                'head_of_department' => true
            ],
            // ... repeat for other teachers, using DepartmentOption enum instead of strings
        ];

        foreach ($teachers as $teacher) {
            Teacher::updateOrCreate(
                ['code' => $teacher['code']], // unique identifier
                [
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                    'phone' => $teacher['phone'],
                    'status' => $teacher['status'],
                    'department' => $teacher['department']->value, // save enum value
                    'head_of_department' => $teacher['head_of_department'],
                ]
            );
        }
    }
}
