<?php

declare(strict_types=1);

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_own_order(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrderFor($user);

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertOk();
    }

    public function test_authenticated_user_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->makeOrderFor($owner);

        $this->actingAs($stranger)
            ->get(route('account.orders.show', $order))
            ->assertForbidden();
    }

    public function test_authenticated_user_cannot_download_another_users_invoice(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->makeOrderFor($owner);

        $this->actingAs($stranger)
            ->get(route('account.orders.invoice', $order))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_account_orders(): void
    {
        $this->get(route('account.orders.index'))
            ->assertRedirect(route('login'));
    }

    protected function makeOrderFor(User $user): Order
    {
        $order = new Order;
        $order->forceFill([
            'user_id' => $user->id,
            'number' => 'C2C-OWN-'.uniqid(),
            'status' => 'pending_payment',
            'email' => $user->email,
            'name' => $user->name,
            'shipping_line1' => 'Street 1',
            'shipping_postal_code' => '1234AB',
            'shipping_city' => 'Amsterdam',
            'shipping_country_code' => 'NL',
            'subtotal_cents' => 200,
            'shipping_cents' => 395,
            'total_cents' => 595,
            'shipping_method_name' => 'PostNL',
            'payment_method' => 'ideal',
            'payment_provider' => 'manual',
        ])->save();

        return $order;
    }
}
