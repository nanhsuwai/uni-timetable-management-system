<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('program_id')->constrained('academic_programs')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_levels');
    }
};
