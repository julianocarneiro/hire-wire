<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('type', 32);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'type']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bank_accounts ADD CONSTRAINT bank_accounts_balance_non_negative CHECK (balance >= 0)');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bank_accounts DROP CONSTRAINT IF EXISTS bank_accounts_balance_non_negative');
        }

        Schema::dropIfExists('bank_accounts');
    }
};
