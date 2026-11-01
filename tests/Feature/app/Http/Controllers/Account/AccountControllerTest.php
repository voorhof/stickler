<?php

use App\Models\Order;
use App\Models\User;

describe('account dashboard', function () {
    test('unauthenticated users are redirected to login', function () {
        $response = $this->get(route('account.dashboard'));

        $response->assertRedirect(route('login'));
    });

    test('unverified users are redirected to email verification', function () {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertRedirect(route('verification.notice'));
    });

    test('verified users can view the dashboard', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertViewIs('account.dashboard');
    });

    test('dashboard sets the page title', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee('Dashboard');
    });

    test('dashboard shows logout button', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee(__('Log out'));
    });

    test('logout from dashboard logs out the user', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
    });

    test('offcanvas shows logout link for authenticated users', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('welcome.index'));

        $response->assertSee(__('Log out'));
    });

    test('offcanvas does not show logout link for guests', function () {
        $response = $this->get(route('welcome.index'));

        $response->assertDontSee(__('Log out'));
    });
});

describe('order history', function () {
    test('unauthenticated users are redirected to login', function () {
        $response = $this->get(route('account.orders.index'));

        $response->assertRedirect(route('login'));
    });

    test('user can view their order history', function () {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.orders.index'));

        $response->assertSuccessful()
            ->assertViewIs('account.orders.index')
            ->assertSee(__('My orders'));
    });

    test('order history shows only users orders', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Order::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('account.orders.index'));

        $response->assertViewHas('orders', function ($orders) use ($user) {
            return $orders->every(fn ($order) => $order->user_id === $user->id);
        });
    });

    test('order history shows empty message when no orders', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.orders.index'));

        $response->assertSuccessful()
            ->assertSee(__('You have not placed any orders yet.'));
    });

    test('user can view order detail', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.orders.show', $order));

        $response->assertSuccessful()
            ->assertViewIs('account.orders.show')
            ->assertSee($order->order_number);
    });

    test('user cannot view another users order', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('account.orders.show', $order));

        $response->assertForbidden();
    });

    test('dashboard shows link to orders', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee(__('My orders'))
            ->assertSee(route('account.orders.index'));
    });
});
