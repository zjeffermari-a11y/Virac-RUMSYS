<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            // Add composite index for faster queries on outstanding bills
            // This index helps queries like: WHERE stall_id = X AND status = 'unpaid'
            if (!$this->indexExists('billing', 'billing_stall_id_status_index')) {
                $table->index(['stall_id', 'status'], 'billing_stall_id_status_index');
            }
            
            // Add index on due_date for sorting
            if (!$this->indexExists('billing', 'billing_due_date_index')) {
                $table->index('due_date', 'billing_due_date_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            if ($this->indexExists('billing', 'billing_stall_id_status_index')) {
                $table->dropIndex('billing_stall_id_status_index');
            }
            if ($this->indexExists('billing', 'billing_due_date_index')) {
                $table->dropIndex('billing_due_date_index');
            }
        });
    }
    
    private function indexExists($table, $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        if ($connection->getDriverName() === 'mysql') {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            return $result[0]->count > 0;
        } elseif ($connection->getDriverName() === 'pgsql') {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM pg_indexes 
                 WHERE schemaname = 'public' AND tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return $result[0]->count > 0;
        }
        
        return false;
    }
};

