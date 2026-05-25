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
        Schema::table('billing', function (Blueprint $table) {
            // Add columns for detailed bill breakdown, especially for utilities.
            // These can be nullable because they won't apply to all billing types (e.g., Rent).
            $table->decimal('previous_reading', 10, 2)->nullable()->after('amount');
            $table->decimal('current_reading', 10, 2)->nullable()->after('previous_reading');
            $table->decimal('consumption', 10, 2)->nullable()->after('current_reading');
            $table->decimal('rate', 10, 4)->nullable()->after('consumption'); // Rate might need more precision
            $table->decimal('other_fees', 10, 2)->nullable()->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            // Drop the columns if the migration is rolled back.
            $table->dropColumn([
                'previous_reading',
                'current_reading',
                'consumption',
                'rate',
            ]);
        });
    }
};
