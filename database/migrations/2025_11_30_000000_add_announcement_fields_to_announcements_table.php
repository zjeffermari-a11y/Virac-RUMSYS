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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('announcement_type')->nullable()->after('content');
            $table->string('related_section')->nullable()->after('announcement_type');
            $table->string('related_utility')->nullable()->after('related_section');
            $table->unsignedBigInteger('related_stall_id')->nullable()->after('related_utility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['announcement_type', 'related_section', 'related_utility', 'related_stall_id']);
        });
    }
};

