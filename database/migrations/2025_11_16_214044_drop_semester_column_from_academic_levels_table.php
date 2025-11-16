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
            // Drop the semester column
            $table->dropColumn('semester');
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
            // Restore the semester column (adjust type and position as needed)
            $table->string('semester')->nullable()->after('name');
        });

    }
};
