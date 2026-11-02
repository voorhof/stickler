<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee(__('My orders'))
            ->assertSee(route('account.orders.index'));
    });

    test('dashboard shows user profile details', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee(__('Edit profile'))
            ->assertSee(route('account.profile'));
    });

    test('dashboard shows latest 2 orders', function () {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertViewHas('latestOrders', function ($ordersView) use ($orders) {
                $expectedIds = $orders->sortByDesc('created_at')->take(2)->pluck('id')->sort()->toArray();
                $actualIds = $ordersView->pluck('id')->sort()->toArray();

                return $ordersView->count() === 2
                    && $actualIds === $expectedIds;
            });
    });

    test('dashboard shows start shopping when no orders', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertSee(__('You have not placed any orders yet.'))
            ->assertSee(__('Start shopping to place your first order.'))
            ->assertSee(__('Start shopping'))
            ->assertSee(route('products.index'));
    });

    test('dashboard shows view cart when cart has items and no orders', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'is_guest' => false]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertSee(__('View cart'))
            ->assertSee(route('cart.index'));
    });

    test('dashboard shows order details without links', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee($order->status->getLabel());
    });
});

describe('profile edit', function () {
    test('unauthenticated users are redirected to login', function () {
        $response = $this->get(route('account.profile'));

        $response->assertRedirect(route('login'));
    });

    test('user can view the profile edit page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.profile'));

        $response->assertSuccessful()
            ->assertViewIs('account.profile.edit')
            ->assertSee(__('Edit profile'))
            ->assertSee($user->name)
            ->assertSee($user->email);
    });

    test('user can update name and email', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('account.profile'))
            ->assertSessionHas('status', 'profile-updated');

        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@example.com');
    });

    test('changing email resets verification status', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'email' => 'newemail@example.com',
        ]);

        $user->refresh();
        expect($user->email)->toBe('newemail@example.com')
            ->and($user->email_verified_at)->toBeNull();
    });

    test('changing email sends verification notification', function () {
        $user = User::factory()->create();

        Illuminate\Support\Facades\Notification::fake();

        $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'email' => 'newemail@example.com',
        ]);

        $user->refresh();
        Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            Illuminate\Auth\Notifications\VerifyEmail::class,
        );
    });

    test('updating name without changing email keeps verification status', function () {
        $user = User::factory()->create();
        $originalVerifiedAt = $user->email_verified_at;

        $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email_verified_at)->toEqual($originalVerifiedAt);
    });

    test('user can update password with current password confirmation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertRedirect(route('account.profile'));

        $user->refresh();
        expect(Hash::check('newpassword', $user->password))->toBeTrue();
    });

    test('user cannot update password without current password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    });

    test('user cannot use duplicate email', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'email' => $otherUser->email,
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('profile update shows success message', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response = $this->actingAs($user)->get(route('account.profile'));

        $response->assertSee(__('Your profile has been updated successfully.'));
    });

    test('profile page shows back to dashboard link', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.profile'));

        $response->assertSee(__('Back to dashboard'))
            ->assertSee(route('account.dashboard'));
    });
});
