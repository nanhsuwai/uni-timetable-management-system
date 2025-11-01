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
        // First, modify the enum to include new values
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active', 'inactive', 'archived', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");

        // Then update existing statuses
        DB::statement("UPDATE teachers SET status = 'approved' WHERE status = 'active'");
        DB::statement("UPDATE teachers SET status = 'pending' WHERE status IN ('inactive', 'archived')");

        // Finally, modify the enum to only include the new values
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // First, update statuses back to the old enum values
        DB::statement("UPDATE teachers SET status = 'active' WHERE status = 'approved'");
        DB::statement("UPDATE teachers SET status = 'inactive' WHERE status IN ('pending', 'rejected')");

        // Then modify the enum back
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active'");
    }
};
