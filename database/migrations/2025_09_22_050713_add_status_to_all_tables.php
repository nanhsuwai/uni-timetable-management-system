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
        // Add status field to academic_years table
        Schema::table('academic_years', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('end_date');
        });

        // Add status field to academic_programs table
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('name');
        });

        // Add status field to semesters table
        Schema::table('semesters', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('name');
        });

        // Add status field to academic_levels table
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('name');
        });

        // Add status field to sections table
        Schema::table('sections', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('name');
        });

        // Add status field to classrooms table
        Schema::table('classrooms', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('room_no');
        });

        // Add status field to subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('name');
        });

        // Add status field to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('phone');
        });

        // Add status field to timetable_entries table
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove status field from academic_years table
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from academic_programs table
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from semesters table
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from academic_levels table
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from sections table
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from classrooms table
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Remove status field from timetable_entries table
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
