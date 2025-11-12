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
            $table->string('code');
            $table->string('name');
            $table->string('level')->nullable();
            /* $table->string('program')->nullable(); */
            $table->string('semester')->nullable();
            $table->timestamps();

            $table->unique(['code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
