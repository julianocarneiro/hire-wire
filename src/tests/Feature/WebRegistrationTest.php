<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_CPF = '529.982.247-25';

    private const VALID_CPF_NORMALIZED = '52998224725';

    public function test_guest_can_view_register_page(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Auth/Register')
            );
    }

    public function test_valid_registration_creates_user_logs_in_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Cliente Novo',
            'email' => 'novo@example.test',
            'cpf' => self::VALID_CPF,
            'password' => 'senha-valida-8',
            'password_confirmation' => 'senha-valida-8',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'novo@example.test',
            'cpf' => self::VALID_CPF_NORMALIZED,
            'name' => 'Cliente Novo',
        ]);

        $this->assertSame(1, User::query()->count());
    }

    public function test_registration_fails_when_email_is_duplicate(): void
    {
        User::factory()->create([
            'email' => 'exists@example.test',
        ]);

        $this->post('/register', [
            'name' => 'Outro',
            'email' => 'exists@example.test',
            'cpf' => self::VALID_CPF,
            'password' => 'senha-valida-8',
            'password_confirmation' => 'senha-valida-8',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::query()->count());
        $this->assertGuest();
    }

    public function test_registration_fails_when_cpf_is_duplicate(): void
    {
        User::factory()->create([
            'email' => 'outro@example.test',
            'cpf' => self::VALID_CPF_NORMALIZED,
        ]);

        $this->post('/register', [
            'name' => 'Outro Nome',
            'email' => 'unico@example.test',
            'cpf' => self::VALID_CPF,
            'password' => 'senha-valida-8',
            'password_confirmation' => 'senha-valida-8',
        ])->assertSessionHasErrors('cpf');

        $this->assertSame(1, User::query()->count());
        $this->assertGuest();
    }

    public function test_registration_fails_when_cpf_is_invalid(): void
    {
        $this->post('/register', [
            'name' => 'Cliente Novo',
            'email' => 'novo@example.test',
            'cpf' => '11111111111',
            'password' => 'senha-valida-8',
            'password_confirmation' => 'senha-valida-8',
        ])->assertSessionHasErrors('cpf');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/register')
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_page_renders_inertia_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }
}
