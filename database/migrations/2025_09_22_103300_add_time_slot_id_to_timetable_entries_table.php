<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable()->constrained('time_slots')->onDelete('cascade')->after('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropForeign(['time_slot_id']);
            $table->dropColumn('time_slot_id');
        });
    }
};
