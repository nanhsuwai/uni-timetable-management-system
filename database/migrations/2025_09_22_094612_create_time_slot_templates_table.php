<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_slot_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_number')->unique();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['period_number']);
        });

        // Insert default time slot templates based on the user's timetable
        DB::table('time_slot_templates')->insert([
            [
                'name' => 'Period 1',
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'period_number' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Period 2',
                'start_time' => '10:05:00',
                'end_time' => '11:05:00',
                'period_number' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Period 3',
                'start_time' => '11:10:00',
                'end_time' => '12:10:00',
                'period_number' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Period 4',
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'period_number' => 4,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Period 5',
                'start_time' => '14:05:00',
                'end_time' => '15:05:00',
                'period_number' => 5,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Period 6',
                'start_time' => '15:10:00',
                'end_time' => '16:10:00',
                'period_number' => 6,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_slot_templates');
    }
};
