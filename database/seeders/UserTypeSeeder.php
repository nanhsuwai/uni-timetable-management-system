<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserTypeSeeder extends Seeder
{
    public function run() : void
    {
        $user_types = json_decode(file_get_contents(public_path() . '/js/user-types.json'), true);
        Type::truncate();
        foreach ($user_types['data'] as $type) {
            Type::create([
                'name' => $type['name'],
                'code' => Str::slug($type['name'], '_')
            ]);
        }
    }
}
