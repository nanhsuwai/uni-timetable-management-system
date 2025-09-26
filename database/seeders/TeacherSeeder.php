<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teachers = [
            ['code' => 'T001', 'name' => 'Dr. Alice Johnson', 'email' => 'alice.johnson@ucsh.edu.mm', 'phone' => '+959123456789', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => true],
            ['code' => 'T002', 'name' => 'Prof. Bob Smith', 'email' => 'bob.smith@ucsh.edu.mm', 'phone' => '+959123456790', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => false],
            ['code' => 'T003', 'name' => 'Ms. Carol Davis', 'email' => 'carol.davis@ucsh.edu.mm', 'phone' => '+959123456791', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => false],
            ['code' => 'T004', 'name' => 'Dr. David Wilson', 'email' => 'david.wilson@ucsh.edu.mm', 'phone' => '+959123456792', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => true],
            ['code' => 'T005', 'name' => 'Prof. Eve Brown', 'email' => 'eve.brown@ucsh.edu.mm', 'phone' => '+959123456793', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => false],
            ['code' => 'T006', 'name' => 'Mr. Frank Miller', 'email' => 'frank.miller@ucsh.edu.mm', 'phone' => '+959123456794', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => false],
            ['code' => 'T007', 'name' => 'Dr. Grace Lee', 'email' => 'grace.lee@ucsh.edu.mm', 'phone' => '+959123456795', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T008', 'name' => 'Prof. Henry Garcia', 'email' => 'henry.garcia@ucsh.edu.mm', 'phone' => '+959123456796', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T009', 'name' => 'Ms. Iris Taylor', 'email' => 'iris.taylor@ucsh.edu.mm', 'phone' => '+959123456797', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T010', 'name' => 'Dr. Jack Anderson', 'email' => 'jack.anderson@ucsh.edu.mm', 'phone' => '+959123456798', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => true],
            ['code' => 'T011', 'name' => 'Ms. Jane Doe', 'email' => 'jane.doe@ucsh.edu.mm', 'phone' => '+959123456799', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => false],
            ['code' => 'T012', 'name' => 'Prof. John Kim', 'email' => 'john.kim@ucsh.edu.mm', 'phone' => '+959123456800', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => false],
            ['code' => 'T013', 'name' => 'Dr. Lisa Chen', 'email' => 'lisa.chen@ucsh.edu.mm', 'phone' => '+959123456801', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => true],
            ['code' => 'T014', 'name' => 'Mr. Mike Rodriguez', 'email' => 'mike.rodriguez@ucsh.edu.mm', 'phone' => '+959123456802', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => false],
            ['code' => 'T015', 'name' => 'Ms. Nancy Patel', 'email' => 'nancy.patel@ucsh.edu.mm', 'phone' => '+959123456803', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => false],
            ['code' => 'T016', 'name' => 'Prof. Oliver Wong', 'email' => 'oliver.wong@ucsh.edu.mm', 'phone' => '+959123456804', 'status' => 'active', 'department' => 'Departments of Language (English and Myanmar)', 'head_of_department' => false],
            ['code' => 'T017', 'name' => 'Dr. Paula Martinez', 'email' => 'paula.martinez@ucsh.edu.mm', 'phone' => '+959123456805', 'status' => 'active', 'department' => 'Departments of Language (English and Myanmar)', 'head_of_department' => false],
            ['code' => 'T018', 'name' => 'Mr. Quentin Lee', 'email' => 'quentin.lee@ucsh.edu.mm', 'phone' => '+959123456806', 'status' => 'active', 'department' => 'Departments of Language (English and Myanmar)', 'head_of_department' => false],
            ['code' => 'T019', 'name' => 'Ms. Rachel Kim', 'email' => 'rachel.kim@ucsh.edu.mm', 'phone' => '+959123456807', 'status' => 'active', 'department' => 'Department of Information Technology Supporting and Maintenance', 'head_of_department' => true],
            ['code' => 'T020', 'name' => 'Prof. Samuel Garcia', 'email' => 'samuel.garcia@ucsh.edu.mm', 'phone' => '+959123456808', 'status' => 'active', 'department' => 'Department of Information Technology Supporting and Maintenance', 'head_of_department' => false],
            ['code' => 'T021', 'name' => 'Dr. Tina Nguyen', 'email' => 'tina.nguyen@ucsh.edu.mm', 'phone' => '+959123456809', 'status' => 'active', 'department' => 'Department of Information Technology Supporting and Maintenance', 'head_of_department' => false],
            ['code' => 'T022', 'name' => 'Mr. Umar Khan', 'email' => 'umar.khan@ucsh.edu.mm', 'phone' => '+959123456810', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => false],
            ['code' => 'T023', 'name' => 'Ms. Vera Lopez', 'email' => 'vera.lopez@ucsh.edu.mm', 'phone' => '+959123456811', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => false],
            ['code' => 'T024', 'name' => 'Prof. Walter Brown', 'email' => 'walter.brown@ucsh.edu.mm', 'phone' => '+959123456812', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => false],
            ['code' => 'T025', 'name' => 'Dr. Xia Zhang', 'email' => 'xia.zhang@ucsh.edu.mm', 'phone' => '+959123456813', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => false],
            ['code' => 'T026', 'name' => 'Mr. Yves Dubois', 'email' => 'yves.dubois@ucsh.edu.mm', 'phone' => '+959123456814', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T027', 'name' => 'Ms. Zoe Taylor', 'email' => 'zoe.taylor@ucsh.edu.mm', 'phone' => '+959123456815', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T028', 'name' => 'Prof. Alan White', 'email' => 'alan.white@ucsh.edu.mm', 'phone' => '+959123456816', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => false],
            ['code' => 'T029', 'name' => 'Dr. Betty Green', 'email' => 'betty.green@ucsh.edu.mm', 'phone' => '+959123456817', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => false],
            ['code' => 'T030', 'name' => 'Mr. Carl Black', 'email' => 'carl.black@ucsh.edu.mm', 'phone' => '+959123456818', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => false],
            ['code' => 'T031', 'name' => 'Ms. Dana Red', 'email' => 'dana.red@ucsh.edu.mm', 'phone' => '+959123456819', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => false],
            ['code' => 'T032', 'name' => 'Prof. Ethan Blue', 'email' => 'ethan.blue@ucsh.edu.mm', 'phone' => '+959123456820', 'status' => 'active', 'department' => 'Departments of Language (English and Myanmar)', 'head_of_department' => false],
            ['code' => 'T033', 'name' => 'Dr. Fiona Gray', 'email' => 'fiona.gray@ucsh.edu.mm', 'phone' => '+959123456821', 'status' => 'active', 'department' => 'Departments of Language (English and Myanmar)', 'head_of_department' => false],
            ['code' => 'T034', 'name' => 'Mr. Gary Yellow', 'email' => 'gary.yellow@ucsh.edu.mm', 'phone' => '+959123456822', 'status' => 'active', 'department' => 'Department of Information Technology Supporting and Maintenance', 'head_of_department' => false],
            ['code' => 'T035', 'name' => 'Ms. Helen Purple', 'email' => 'helen.purple@ucsh.edu.mm', 'phone' => '+959123456823', 'status' => 'active', 'department' => 'Department of Information Technology Supporting and Maintenance', 'head_of_department' => false],
            ['code' => 'T036', 'name' => 'Prof. Ian Orange', 'email' => 'ian.orange@ucsh.edu.mm', 'phone' => '+959123456824', 'status' => 'active', 'department' => 'Faculty of Computer Science', 'head_of_department' => false],
            ['code' => 'T037', 'name' => 'Dr. Julia Pink', 'email' => 'julia.pink@ucsh.edu.mm', 'phone' => '+959123456825', 'status' => 'active', 'department' => 'Faculty of Computer Systems and Technologies', 'head_of_department' => false],
            ['code' => 'T038', 'name' => 'Mr. Kevin Brown', 'email' => 'kevin.brown@ucsh.edu.mm', 'phone' => '+959123456826', 'status' => 'active', 'department' => 'Faculty of Information Science', 'head_of_department' => false],
            ['code' => 'T039', 'name' => 'Ms. Laura Green', 'email' => 'laura.green@ucsh.edu.mm', 'phone' => '+959123456827', 'status' => 'active', 'department' => 'Faculty of Computing', 'head_of_department' => false],
            ['code' => 'T040', 'name' => 'Prof. Mark White', 'email' => 'mark.white@ucsh.edu.mm', 'phone' => '+959123456828', 'status' => 'active', 'department' => 'Department of Natural Science', 'head_of_department' => false],
        ];

        foreach ($teachers as $teacher) {
            \App\Models\Teacher::firstOrCreate([
                'code' => $teacher['code'],
            ], [
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'phone' => $teacher['phone'],
                'status' => $teacher['status'],
                'department' => $teacher['department'],
                'head_of_department' => $teacher['head_of_department'],
            ]);
        }
    }
}
