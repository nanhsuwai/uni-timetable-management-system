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
        DB::statement("ALTER TABLE academic_programs MODIFY COLUMN program_type ENUM('CST', 'CS', 'CT', 'Diploma', 'Master') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE academic_programs MODIFY COLUMN program_type ENUM('CST', 'CS', 'CT', 'Master') NOT NULL");
    }
};
