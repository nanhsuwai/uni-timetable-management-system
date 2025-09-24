<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run() : void
    {
        DB::table('roles')->insert([
            array(
                'name' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now()
            ),
            array(
                'name' => 'Admin',
                'created_at' => now(),
                'upd ated_at' => now()
            ),
            array(
                'name' => 'User',
                'created_at' => now(),
                'updated_at' => now()
            ),
        ]);
    }
}
