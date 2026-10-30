<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Cart;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as LaravelTelescopeServiceProvider;

test('it extends service provider', function () {
    expect(new AppServiceProvider(app()))->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});

test('it registers Telescope outside production', function () {
    $application = Mockery::mock(Application::class);
    $application->shouldReceive('isProduction')->once()->andReturnFalse();
    $application->shouldReceive('singleton')->once()->with(CartService::class);
    $application->shouldReceive('register')->once()->with(LaravelTelescopeServiceProvider::class);
    $application->shouldReceive('register')->once()->with(TelescopeServiceProvider::class);

    new AppServiceProvider($application)->register();
});

test('it does not register Telescope in production', function () {
    $application = Mockery::mock(Application::class);
    $application->shouldReceive('isProduction')->once()->andReturnTrue();
    $application->shouldReceive('singleton')->once()->with(CartService::class);
    $application->shouldNotReceive('register');

    new AppServiceProvider($application)->register();
});

test('it uses bootstrap five pagination', function () {
    expect(Paginator::$defaultView)->toBe('pagination::bootstrap-5')
        ->and(Paginator::$defaultSimpleView)->toBe('pagination::simple-bootstrap-5');
});

test('it enables strict Eloquent behavior outside production', function () {
    expect(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
});

test('it shares zero cart item count when no cookie is present', function () {
    $response = $this->get('/');

    $response->assertViewHas('cartItemCount', 0);
});

test('it shares cart item count when cart token cookie is present', function () {
    $cart = Cart::factory()->create();
    $cart->cartItems()->create([
        'product_id' => null,
        'product_name' => 'Test Product',
        'sku' => 'TEST',
        'quantity' => 3,
        'unit_price' => 1000,
        'total_price' => 3000,
    ]);
    $cart->cartItems()->create([
        'product_id' => null,
        'product_name' => 'Another Product',
        'sku' => 'TEST2',
        'quantity' => 2,
        'unit_price' => 500,
        'total_price' => 1000,
    ]);

    $response = $this->withCookie('cart_token', $cart->cart_token)
        ->get('/');

    $response->assertViewHas('cartItemCount', 5);
});

test('it shares cart item count of the logged in user cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'is_guest' => false]);
    $cart->cartItems()->create([
        'product_id' => null,
        'product_name' => 'Test Product',
        'sku' => 'TEST',
        'quantity' => 4,
        'unit_price' => 1000,
        'total_price' => 4000,
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertViewHas('cartItemCount', 4);
});
