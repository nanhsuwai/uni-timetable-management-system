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
        Schema::table('academic_levels', function (Blueprint $table) {
            // Add semester_id column with foreign key constraint
            $table->foreignId('semester_id')
                ->nullable()
                ->after('program_id')
                ->constrained('semesters')
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index('semester_id');

            // Optional: Add unique constraint for name, program_id, semester_id combination
            $table->unique(['name', 'program_id', 'semester_id'], 'academic_levels_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('academic_levels_unique');

            // Drop foreign key constraint
            $table->dropForeign(['semester_id']);

            // Drop the column
            $table->dropColumn('semester_id');
        });
    }
};
