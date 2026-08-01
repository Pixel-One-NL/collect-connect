<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $previous = session()->getId();

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect(route('account.orders.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previous, session()->getId());
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertRedirect(route('account.orders.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas(User::class, [
            'email' => 'new@example.com',
            'name' => 'New User',
        ]);
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Dup',
                'email' => 'taken@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('email');
    }
}
