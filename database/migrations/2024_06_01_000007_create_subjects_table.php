<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_id', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
