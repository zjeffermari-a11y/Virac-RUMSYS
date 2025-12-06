<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add index on payment_date for faster date filtering
            if (!$this->indexExists('payments', 'payments_payment_date_index')) {
                $table->index('payment_date', 'payments_payment_date_index');
            }
            
            // Add composite index for faster queries filtering by billing_id and payment_date
            if (!$this->indexExists('payments', 'payments_billing_id_payment_date_index')) {
                $table->index(['billing_id', 'payment_date'], 'payments_billing_id_payment_date_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if ($this->indexExists('payments', 'payments_payment_date_index')) {
                $table->dropIndex('payments_payment_date_index');
            }
            if ($this->indexExists('payments', 'payments_billing_id_payment_date_index')) {
                $table->dropIndex('payments_billing_id_payment_date_index');
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

