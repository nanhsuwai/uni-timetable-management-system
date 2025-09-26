<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Academic Year
            ['name' => 'academic_year:view', 'code' => 'academic_year_view', 'module_name' => 'Academic Year', 'description' => 'View academic years'],
            ['name' => 'academic_year:create', 'code' => 'academic_year_create', 'module_name' => 'Academic Year', 'description' => 'Create academic year'],
            ['name' => 'academic_year:update', 'code' => 'academic_year_update', 'module_name' => 'Academic Year', 'description' => 'Update academic year'],
            ['name' => 'academic_year:delete', 'code' => 'academic_year_delete', 'module_name' => 'Academic Year', 'description' => 'Delete academic year'],
            ['name' => 'academic_year:status', 'code' => 'academic_year_status', 'module_name' => 'Academic Year', 'description' => 'Change academic year status'],

            // Academic Program
            ['name' => 'academic_program:view', 'code' => 'academic_program_view', 'module_name' => 'Academic Program', 'description' => 'View academic programs'],
            ['name' => 'academic_program:create', 'code' => 'academic_program_create', 'module_name' => 'Academic Program', 'description' => 'Create academic program'],
            ['name' => 'academic_program:update', 'code' => 'academic_program_update', 'module_name' => 'Academic Program', 'description' => 'Update academic program'],
            ['name' => 'academic_program:delete', 'code' => 'academic_program_delete', 'module_name' => 'Academic Program', 'description' => 'Delete academic program'],
            ['name' => 'academic_program:status', 'code' => 'academic_program_status', 'module_name' => 'Academic Program', 'description' => 'Change academic program status'],

            // Academic Level
            ['name' => 'academic_level:view', 'code' => 'academic_level_view', 'module_name' => 'Academic Level', 'description' => 'View academic levels'],
            ['name' => 'academic_level:create', 'code' => 'academic_level_create', 'module_name' => 'Academic Level', 'description' => 'Create academic level'],
            ['name' => 'academic_level:update', 'code' => 'academic_level_update', 'module_name' => 'Academic Level', 'description' => 'Update academic level'],
            ['name' => 'academic_level:delete', 'code' => 'academic_level_delete', 'module_name' => 'Academic Level', 'description' => 'Delete academic level'],
            ['name' => 'academic_level:status', 'code' => 'academic_level_status', 'module_name' => 'Academic Level', 'description' => 'Change academic level status'],

            // Section
            ['name' => 'section:view', 'code' => 'section_view', 'module_name' => 'Section', 'description' => 'View sections'],
            ['name' => 'section:create', 'code' => 'section_create', 'module_name' => 'Section', 'description' => 'Create section'],
            ['name' => 'section:update', 'code' => 'section_update', 'module_name' => 'Section', 'description' => 'Update section'],
            ['name' => 'section:delete', 'code' => 'section_delete', 'module_name' => 'Section', 'description' => 'Delete section'],
            ['name' => 'section:status', 'code' => 'section_status', 'module_name' => 'Section', 'description' => 'Change section status'],

            // Classroom
            ['name' => 'classroom:view', 'code' => 'classroom_view', 'module_name' => 'Classroom', 'description' => 'View classrooms'],
            ['name' => 'classroom:create', 'code' => 'classroom_create', 'module_name' => 'Classroom', 'description' => 'Create classroom'],
            ['name' => 'classroom:update', 'code' => 'classroom_update', 'module_name' => 'Classroom', 'description' => 'Update classroom'],
            ['name' => 'classroom:delete', 'code' => 'classroom_delete', 'module_name' => 'Classroom', 'description' => 'Delete classroom'],
            ['name' => 'classroom:status', 'code' => 'classroom_status', 'module_name' => 'Classroom', 'description' => 'Change classroom status'],

            // Semester
            ['name' => 'semester:view', 'code' => 'semester_view', 'module_name' => 'Semester', 'description' => 'View semesters'],
            ['name' => 'semester:create', 'code' => 'semester_create', 'module_name' => 'Semester', 'description' => 'Create semester'],
            ['name' => 'semester:update', 'code' => 'semester_update', 'module_name' => 'Semester', 'description' => 'Update semester'],
            ['name' => 'semester:delete', 'code' => 'semester_delete', 'module_name' => 'Semester', 'description' => 'Delete semester'],
            ['name' => 'semester:status', 'code' => 'semester_status', 'module_name' => 'Semester', 'description' => 'Change semester status'],

            // Subject
            ['name' => 'subject:view', 'code' => 'subject_view', 'module_name' => 'Subject', 'description' => 'View subjects'],
            ['name' => 'subject:create', 'code' => 'subject_create', 'module_name' => 'Subject', 'description' => 'Create subject'],
            ['name' => 'subject:update', 'code' => 'subject_update', 'module_name' => 'Subject', 'description' => 'Update subject'],
            ['name' => 'subject:delete', 'code' => 'subject_delete', 'module_name' => 'Subject', 'description' => 'Delete subject'],
            ['name' => 'subject:assign-teacher', 'code' => 'subject_assign_teacher', 'module_name' => 'Subject', 'description' => 'Assign teacher to subject'],

            // Timetable Entry
            ['name' => 'timetable_entry:view', 'code' => 'timetable_entry_view', 'module_name' => 'Timetable Entry', 'description' => 'View timetable entries'],
            ['name' => 'timetable_entry:grid-view', 'code' => 'timetable_entry_grid_view', 'module_name' => 'Timetable Entry', 'description' => 'View timetable grid'],

            // User
            ['name' => 'user:view', 'code' => 'user_view', 'module_name' => 'User', 'description' => 'View users'],
            ['name' => 'user:create', 'code' => 'user_create', 'module_name' => 'User', 'description' => 'Create user'],
            ['name' => 'user:update', 'code' => 'user_update', 'module_name' => 'User', 'description' => 'Update user'],
            ['name' => 'user:delete', 'code' => 'user_delete', 'module_name' => 'User', 'description' => 'Delete user'],
            ['name' => 'user:create-permission', 'code' => 'user_create_permission', 'module_name' => 'User', 'description' => 'Create user permissions'],
            ['name' => 'user:update-password', 'code' => 'user_update_password', 'module_name' => 'User', 'description' => 'Update user password'],

            // Permission
            ['name' => 'permission:view', 'code' => 'permission_view', 'module_name' => 'Permission', 'description' => 'View permissions'],
            ['name' => 'permission:create', 'code' => 'permission_create', 'module_name' => 'Permission', 'description' => 'Create permissions'],
            ['name' => 'permission:update', 'code' => 'permission_update', 'module_name' => 'Permission', 'description' => 'Update permissions'],
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
