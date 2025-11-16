<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Change name column to nullable
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Revert the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Revert name column to NOT nullable
            $table->string('name')->nullable(false)->change();
        });
    }
};