<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove duplicates, keeping the one with the smallest ID (oldest)
        DB::statement('
            DELETE t1 FROM academic_programs t1
            INNER JOIN academic_programs t2
            WHERE t1.id > t2.id
            AND t1.academic_year_id = t2.academic_year_id
            AND t1.name = t2.name
        ');

        Schema::table('academic_programs', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->dropUnique(['academic_year_id', 'name']);
        });
    }
};
