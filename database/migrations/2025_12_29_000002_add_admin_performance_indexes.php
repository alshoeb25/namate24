<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return (bool) DB::selectOne(
                "SELECT 1 FROM sqlite_master WHERE type='index' AND name=?",
                [$indexName]
            );
        }
        $db = DB::connection()->getDatabaseName();
        return (bool) DB::selectOne(
            'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$db, $table, $indexName]
        );
    }

    public function up(): void
    {
        // Tutor documents indexes
        if (!$this->indexExists('tutor_documents', 'tutor_documents_verification_status_index')) {
            Schema::table('tutor_documents', function (Blueprint $table) {
                $table->index('verification_status', 'tutor_documents_verification_status_index');
            });
        }

        if (!$this->indexExists('tutor_documents', 'tutor_documents_created_at_index')) {
            Schema::table('tutor_documents', function (Blueprint $table) {
                $table->index('created_at', 'tutor_documents_created_at_index');
            });
        }

        // Orders status index
        if (!$this->indexExists('orders', 'orders_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('status', 'orders_status_index');
            });
        }

        // Student requirements lead_status index
        if (!$this->indexExists('student_requirements', 'student_requirements_lead_status_index')) {
            Schema::table('student_requirements', function (Blueprint $table) {
                $table->index('lead_status', 'student_requirements_lead_status_index');
            });
        }

        // Coin transactions type index
        if (!$this->indexExists('coin_transactions', 'coin_transactions_type_index')) {
            Schema::table('coin_transactions', function (Blueprint $table) {
                $table->index('type', 'coin_transactions_type_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('tutor_documents', 'tutor_documents_verification_status_index')) {
            Schema::table('tutor_documents', function (Blueprint $table) {
                $table->dropIndex('tutor_documents_verification_status_index');
            });
        }
        if ($this->indexExists('tutor_documents', 'tutor_documents_created_at_index')) {
            Schema::table('tutor_documents', function (Blueprint $table) {
                $table->dropIndex('tutor_documents_created_at_index');
            });
        }
        if ($this->indexExists('orders', 'orders_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_status_index');
            });
        }
        if ($this->indexExists('student_requirements', 'student_requirements_lead_status_index')) {
            Schema::table('student_requirements', function (Blueprint $table) {
                $table->dropIndex('student_requirements_lead_status_index');
            });
        }
        if ($this->indexExists('coin_transactions', 'coin_transactions_type_index')) {
            Schema::table('coin_transactions', function (Blueprint $table) {
                $table->dropIndex('coin_transactions_type_index');
            });
        }
    }
};
