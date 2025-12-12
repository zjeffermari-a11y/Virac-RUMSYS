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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email')) {
                // Make email nullable and default to null
                $table->string('email')->nullable()->default(null)->change();
            } else {
                // If missing, add it as nullable
                $table->string('email')->nullable()->default(null)->unique()->after('username');
            }
        });
    }

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
