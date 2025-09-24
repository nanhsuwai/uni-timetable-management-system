<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('user_verify_emails', function (Blueprint $table) {
            $table->id();
            $table->longText('hash_code');
            $table->foreignId('user_id');
            $table->enum('type', ['email-verify', 'password-reset']);
            $table->enum('status', ['active', 'inactive']);
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('user_verify_emails');
    }
};
