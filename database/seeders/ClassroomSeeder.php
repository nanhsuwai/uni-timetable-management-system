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
        $roomNumbers = ['101', '102', '103', '104', '105'];

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
