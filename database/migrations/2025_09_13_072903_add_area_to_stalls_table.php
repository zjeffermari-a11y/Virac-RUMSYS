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
        Schema::table('stalls', function (Blueprint $table) {
            // This adds a decimal column named 'area' after the 'table_number' column.
            // It's nullable because not all sections will have an area.
            $table->decimal('area', 8, 2)->nullable()->after('table_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            // This will remove the 'area' column if you ever need to undo the migration.
            $table->dropColumn('area');
        });
    }
};
