<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->has('user')
        );
    }

    public function test_logout_ends_session_and_blocks_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/logout')->assertRedirect(route('login'));

        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_another_users_bank_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $account = BankAccount::factory()->for($userA)->create();

        $this->actingAs($userB)
            ->get(route('bank-accounts.show', $account->id))
            ->assertNotFound();
    }

    public function test_repeated_login_does_not_duplicate_users(): void
    {
        $user = User::factory()->create([
            'email' => 'repeat@example.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => 'repeat@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'repeat@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('users', 1);
    }
}
