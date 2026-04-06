<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BankAccountMovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_movements_page_includes_movements_prop(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'savings',
            'balance' => '10.00',
        ]);

        $this->actingAs($user)
            ->get(route('bank-accounts.movements', $account->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/BankAccountMovements')
                ->has('movements', 0));
    }

    public function test_deposit_on_savings_credits_stated_amount_and_records_movement(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'savings',
            'balance' => '10.00',
        ]);

        $this->actingAs($user)
            ->from(route('bank-accounts.movements', $account->id))
            ->post(route('bank-accounts.deposit', $account->id), [
                'amount' => '50.00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '60.00',
        ]);

        $this->assertDatabaseHas('account_movements', [
            'bank_account_id' => $account->id,
            'type' => 'deposit',
            'amount' => '50.00',
            'balance_after' => '60.00',
        ]);
    }

    public function test_deposit_on_checking_applies_bonus_and_stores_metadata(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'checking',
            'balance' => '0.00',
        ]);

        $this->actingAs($user)
            ->post(route('bank-accounts.deposit', $account->id), [
                'amount' => '100.00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '100.50',
        ]);

        $row = DB::table('account_movements')
            ->where('bank_account_id', $account->id)
            ->where('type', 'deposit')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('100.50', $row->amount);
        $this->assertSame('100.50', $row->balance_after);
        $meta = json_decode((string) $row->metadata, true);
        $this->assertSame('100.00', $meta['stated_amount']);
    }

    public function test_monthly_adjustment_updates_balance_and_records_movement(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'savings',
            'balance' => '10000.00',
        ]);

        $this->actingAs($user)
            ->post(route('bank-accounts.monthly-adjustment', $account->id))
            ->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '10000.10',
        ]);

        $this->assertDatabaseHas('account_movements', [
            'bank_account_id' => $account->id,
            'type' => 'monthly_adjustment',
            'amount' => '0.10',
            'balance_after' => '10000.10',
        ]);
    }

    public function test_user_cannot_deposit_on_another_users_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $account = BankAccount::factory()->for($userA)->create(['balance' => '5.00']);

        $this->actingAs($userB)
            ->post(route('bank-accounts.deposit', $account->id), [
                'amount' => '10.00',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '5.00',
        ]);

        $this->assertSame(0, DB::table('account_movements')->where('bank_account_id', $account->id)->count());
    }

    public function test_user_cannot_apply_adjustment_on_another_users_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $account = BankAccount::factory()->for($userA)->create([
            'type' => 'savings',
            'balance' => '1000.00',
        ]);

        $this->actingAs($userB)
            ->post(route('bank-accounts.monthly-adjustment', $account->id))
            ->assertNotFound();

        $this->assertSame(0, DB::table('account_movements')->where('bank_account_id', $account->id)->count());
    }

    public function test_deposit_with_non_positive_amount_fails_validation(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create(['balance' => '1.00']);

        $this->actingAs($user)
            ->from(route('bank-accounts.movements', $account->id))
            ->post(route('bank-accounts.deposit', $account->id), [
                'amount' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '1.00',
        ]);
    }
}
