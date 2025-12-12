<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        Schema::table('users', function (Blueprint $table) {
            // Logic moved to create_users_table
        });

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to not nullable (might fail if nulls exist)
            $table->string('email')->nullable(false)->change();
        });
    }
};
