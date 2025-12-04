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
        // Fix billing: add amount_after_due
        if (Schema::hasTable('billing') && !Schema::hasColumn('billing', 'amount_after_due')) {
            Schema::table('billing', function (Blueprint $table) {
                $table->decimal('amount_after_due', 10, 2)->nullable()->after('penalty');
            });
        }

        // Fix users: add password_changed_at
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('password_changed_at')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('billing') && Schema::hasColumn('billing', 'amount_after_due')) {
            Schema::table('billing', function (Blueprint $table) {
                $table->dropColumn('amount_after_due');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'password_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_changed_at');
            });
        }
    }
};
