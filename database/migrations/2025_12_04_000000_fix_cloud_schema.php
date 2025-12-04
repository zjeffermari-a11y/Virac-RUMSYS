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
        // Fix audit_trails
        if (Schema::hasTable('audit_trails') && !Schema::hasColumn('audit_trails', 'details')) {
            Schema::table('audit_trails', function (Blueprint $table) {
                $table->text('details')->nullable()->after('result');
            });
        }

        // Fix billing
        if (Schema::hasTable('billing') && !Schema::hasColumn('billing', 'penalty')) {
            Schema::table('billing', function (Blueprint $table) {
                $table->decimal('penalty', 10, 2)->default(0)->after('amount');
            });
        }

        // Fix schedules
        if (Schema::hasTable('schedules') && !Schema::hasColumn('schedules', 'schedule_day')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('schedule_day')->nullable()->after('description');
            });
        }

        // Fix stalls (area) - check if missing
        if (Schema::hasTable('stalls') && !Schema::hasColumn('stalls', 'area')) {
            Schema::table('stalls', function (Blueprint $table) {
                $table->decimal('area', 8, 2)->nullable()->after('table_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_trails') && Schema::hasColumn('audit_trails', 'details')) {
            Schema::table('audit_trails', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }
        if (Schema::hasTable('billing') && Schema::hasColumn('billing', 'penalty')) {
            Schema::table('billing', function (Blueprint $table) {
                $table->dropColumn('penalty');
            });
        }
        if (Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'schedule_day')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('schedule_day');
            });
        }
        if (Schema::hasTable('stalls') && Schema::hasColumn('stalls', 'area')) {
            Schema::table('stalls', function (Blueprint $table) {
                $table->dropColumn('area');
            });
        }
    }
};
