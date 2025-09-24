<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('room_no');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};
