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
        Schema::create('timetable_entry_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_entry_id')->constrained('timetable_entries')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['timetable_entry_id', 'teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timetable_entry_teacher');
    }
};
