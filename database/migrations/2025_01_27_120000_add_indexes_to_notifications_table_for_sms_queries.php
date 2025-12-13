<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add composite index for faster SMS queries
            // This index helps queries like: WHERE channel = 'sms' AND status = 'sent' AND title IN (...)
            if (!$this->indexExists('notifications', 'notifications_channel_status_title_index')) {
                $table->index(['channel', 'status', 'title'], 'notifications_channel_status_title_index');
            }
            
            // Add index on sent_at for sorting
            if (!$this->indexExists('notifications', 'notifications_sent_at_index')) {
                $table->index('sent_at', 'notifications_sent_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'notifications_channel_status_title_index')) {
                $table->dropIndex('notifications_channel_status_title_index');
            }
            if ($this->indexExists('notifications', 'notifications_sent_at_index')) {
                $table->dropIndex('notifications_sent_at_index');
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
