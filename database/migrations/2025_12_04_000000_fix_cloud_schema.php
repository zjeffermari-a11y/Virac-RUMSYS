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
        if (Schema::hasTable('billing')) {
            Schema::table('billing', function (Blueprint $table) {
                if (!Schema::hasColumn('billing', 'penalty')) {
                    $table->decimal('penalty', 10, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn('billing', 'amount_after_due')) {
                    $table->decimal('amount_after_due', 10, 2)->nullable()->after('penalty');
                }
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
        if (Schema::hasTable('billing')) {
            Schema::table('billing', function (Blueprint $table) {
                if (Schema::hasColumn('billing', 'penalty')) {
                    $table->dropColumn('penalty');
                }
                if (Schema::hasColumn('billing', 'amount_after_due')) {
                    $table->dropColumn('amount_after_due');
                }
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
