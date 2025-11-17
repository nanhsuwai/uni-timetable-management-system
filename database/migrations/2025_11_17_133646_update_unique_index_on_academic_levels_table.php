<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {



            $table->unique(['program_id', 'name', 'semester_id'], 'academic_levels_unique');
        });
    }

    public function down(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->dropUnique('academic_levels_unique');
        });
    }
};
