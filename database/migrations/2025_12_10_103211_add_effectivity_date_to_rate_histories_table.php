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
        Schema::table('rate_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('rate_histories', 'effectivity_date')) {
                $table->date('effectivity_date')->nullable()->after('changed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_histories', function (Blueprint $table) {
            if (Schema::hasColumn('rate_histories', 'effectivity_date')) {
                $table->dropColumn('effectivity_date');
            }
        });
    }
};
