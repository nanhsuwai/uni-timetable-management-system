<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roomNumbers = ['201(နှစ်ထပ်ဆောင်)', '202(နှစ်ထပ်ဆောင်)', '302(လေးထပ်ဆောင်)', '401(လေးထပ်ဆောင်)', ' 102 (လေးထပ်ဆောင်)','103(လေးထပ်ဆောင်)','101(လေးထပ်ဆောင်)','202 (လေးထပ်ဆောင်)','203 (လေးထပ်ဆောင်)','201 (လေးထပ်ဆောင်)','303 (လေးထပ်ဆောင်)','301 (လေးထပ်ဆောင်)'];

        foreach ($roomNumbers as $roomNo) {
            \App\Models\Classroom::firstOrCreate([
                'room_no' => $roomNo,
            ], [
                'level_id' => null,
                'section_id' => null,
                'status' => 'active',
                'is_available' => true,
            ]);
        }
    }
}
