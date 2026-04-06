<?php

namespace Tests\Feature\OAuth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PassportProtectedApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:keys', ['--force' => true]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_rejects_invalid_bearer_token(): void
    {
        $this->withHeader('Authorization', 'Bearer not-a-valid-token')
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_me_returns_profile_with_valid_token_and_scope(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user, ['read:profile']);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_forbids_token_without_required_scope(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user, ['read:accounts']);

        $this->getJson('/api/me')->assertForbidden();
    }
}
