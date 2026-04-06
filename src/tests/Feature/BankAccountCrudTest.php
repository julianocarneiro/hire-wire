<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_bank_accounts_props(): void
    {
        $user = User::factory()->create();
        BankAccount::factory()->for($user)->create([
            'type' => 'savings',
            'balance' => '100.00',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('bankAccounts', 1)
                ->where('bankAccounts.0.type', 'savings')
            );
    }

    public function test_user_can_create_bank_account_via_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('bank-accounts.store'), [
                'type' => 'checking',
                'balance' => '250.50',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $user->id,
            'type' => 'checking',
            'balance' => '250.50',
        ]);
    }

    public function test_duplicate_account_type_per_user_is_rejected(): void
    {
        $user = User::factory()->create();
        BankAccount::factory()->for($user)->create(['type' => 'savings']);

        $this->actingAs($user)
            ->post(route('bank-accounts.store'), [
                'type' => 'savings',
                'balance' => '0',
            ])
            ->assertSessionHasErrors('type');
    }

    public function test_user_can_update_bank_account_balance(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'investments',
            'balance' => '10.00',
        ]);

        $this->actingAs($user)
            ->from(route('bank-accounts.show', $account->id))
            ->patch(route('bank-accounts.update', $account->id), [
                'balance' => '99.99',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '99.99',
        ]);
    }

    public function test_user_can_delete_own_bank_account(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('bank-accounts.destroy', $account->id))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
    }

    public function test_user_can_view_bank_account_movements_page(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::factory()->for($user)->create([
            'type' => 'savings',
            'balance' => '42.00',
        ]);

        $this->actingAs($user)
            ->get(route('bank-accounts.movements', $account->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/BankAccountMovements')
                ->where('account.id', $account->id)
                ->where('account.type', 'savings')
            );
    }

    public function test_user_cannot_view_movements_of_another_users_bank_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $account = BankAccount::factory()->for($userA)->create();

        $this->actingAs($userB)
            ->get(route('bank-accounts.movements', $account->id))
            ->assertNotFound();
    }

    public function test_user_cannot_alter_another_users_bank_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $account = BankAccount::factory()->for($userA)->create(['balance' => '5.00']);

        $this->actingAs($userB)
            ->patch(route('bank-accounts.update', $account->id), [
                'balance' => '999.00',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'balance' => '5.00',
        ]);
    }
}
