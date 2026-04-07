<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supports listForAccount: filter by bank_account_id and order by created_at desc.
     */
    public function up(): void
    {
        Schema::table('account_movements', function (Blueprint $table) {
            $table->index(['bank_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('account_movements', function (Blueprint $table) {
            $table->dropIndex(['bank_account_id', 'created_at']);
        });
    }
};
