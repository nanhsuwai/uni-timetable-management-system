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
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
        });

        // Update existing semesters to have a default academic year
        $defaultAcademicYear = \App\Models\AcademicYear::getActiveYears()->first();
        if ($defaultAcademicYear) {
            \Illuminate\Support\Facades\DB::table('semesters')->whereNull('academic_year_id')->update(['academic_year_id' => $defaultAcademicYear->id]);
        }

        // Make the column non-nullable now that all records have a value
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
