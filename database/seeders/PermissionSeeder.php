<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard:access', 'code' => 'dashboard_access', 'module_name' => 'Dashboard', 'description' => 'Access dashboard'],
            ['name' => 'academic_year:manage', 'code' => 'academic_year_manage', 'module_name' => 'Academic Year', 'description' => 'Manage academic years'],
            ['name' => 'time_slot:manage', 'code' => 'time_slot_manage', 'module_name' => 'Time Slot', 'description' => 'Manage time slots'],
            ['name' => 'academic_level:manage', 'code' => 'academic_level_manage', 'module_name' => 'Academic Level', 'description' => 'Manage academic levels'],
            ['name' => 'classroom:manage', 'code' => 'classroom_manage', 'module_name' => 'Classroom', 'description' => 'Manage classrooms'],
            ['name' => 'timetable_entry:manage', 'code' => 'timetable_entry_manage', 'module_name' => 'Timetable Entry', 'description' => 'Manage timetable entries'],
            ['name' => 'subject:manage', 'code' => 'subject_manage', 'module_name' => 'Subject', 'description' => 'Manage subjects'],
            ['name' => 'teacher:manage', 'code' => 'teacher_manage', 'module_name' => 'Teacher', 'description' => 'Manage teachers'],
            ['name' => 'user:manage', 'code' => 'user_manage', 'module_name' => 'User', 'description' => 'Manage users'],
            ['name' => 'academic_program:manage', 'code' => 'academic_program_manage', 'module_name' => 'Academic Program', 'description' => 'Manage academic programs'],
            ['name' => 'teacher_assign:manage', 'code' => 'teacher_assign_manage', 'module_name' => 'Teacher Assignment', 'description' => 'Manage teacher assignments'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'code' => $permission['code'],
                    'module_name' => $permission['module_name'],
                    'description' => $permission['description']
                ]
            );
        }
    }
}
