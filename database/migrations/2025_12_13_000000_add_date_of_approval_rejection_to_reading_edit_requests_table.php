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
        Schema::table('reading_edit_requests', function (Blueprint $table) {
            $table->timestamp('date_of_approval_rejection')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_edit_requests', function (Blueprint $table) {
            $table->dropColumn('date_of_approval_rejection');
        });
    }
};
