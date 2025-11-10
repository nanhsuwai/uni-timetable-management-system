<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-12-01',
            'end_date' => '2025-9-30',
            'status' => 'active',
        ]);
    }
}
