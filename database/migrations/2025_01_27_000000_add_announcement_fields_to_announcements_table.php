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
            if (!Schema::hasColumn('announcements', 'announcement_type')) {
                $table->string('announcement_type')->nullable()->after('content');
            }
            if (!Schema::hasColumn('announcements', 'related_section')) {
                $table->string('related_section')->nullable()->after('announcement_type');
            }
            if (!Schema::hasColumn('announcements', 'related_utility')) {
                $table->string('related_utility')->nullable()->after('related_section');
            }
            if (!Schema::hasColumn('announcements', 'related_stall_id')) {
                $table->unsignedBigInteger('related_stall_id')->nullable()->after('related_utility');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('announcements', 'announcement_type')) {
                $columnsToDrop[] = 'announcement_type';
            }
            if (Schema::hasColumn('announcements', 'related_section')) {
                $columnsToDrop[] = 'related_section';
            }
            if (Schema::hasColumn('announcements', 'related_utility')) {
                $columnsToDrop[] = 'related_utility';
            }
            if (Schema::hasColumn('announcements', 'related_stall_id')) {
                $columnsToDrop[] = 'related_stall_id';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

