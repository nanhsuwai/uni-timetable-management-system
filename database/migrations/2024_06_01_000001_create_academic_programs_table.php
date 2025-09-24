<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_programs');
    }
};
