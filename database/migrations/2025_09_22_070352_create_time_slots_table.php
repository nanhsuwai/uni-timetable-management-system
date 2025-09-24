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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->index(['academic_year_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_slots');
    }
};
