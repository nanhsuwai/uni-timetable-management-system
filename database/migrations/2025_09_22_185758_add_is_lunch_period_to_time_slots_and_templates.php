<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->boolean('is_lunch_period')->default(false)->after('end_time');
        });

        Schema::table('time_slot_templates', function (Blueprint $table) {
            $table->boolean('is_lunch_period')->default(false)->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn('is_lunch_period');
        });

        Schema::table('time_slot_templates', function (Blueprint $table) {
            $table->dropColumn('is_lunch_period');
        });
    }
};
