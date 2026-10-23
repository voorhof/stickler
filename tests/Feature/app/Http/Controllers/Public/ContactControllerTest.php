<?php

use App\Mail\ContactMessageReceived;
use App\Models\Message;
use Database\Seeders\BusinessHoursSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;
use LaBoiteACode\FilamentBusinessHours\Support\Weekdays;
use Spatie\Honeypot\Events\SpamDetectedEvent;
use Spatie\OpeningHours\OpeningHours;

test('it can view the contact page without business hours', function () {
    $response = $this->get(route('contact.index'));

    $response->assertSuccessful()
        ->assertViewIs('public.contact')
        ->assertViewHas('openingHours', fn ($value) => $value === null)
        ->assertViewHas('weekdays', Weekdays::DAYS);
});

test('it can view the contact page with business hours', function () {
    (new BusinessHoursSeeder)->run();

    $response = $this->get(route('contact.index'));

    $response->assertSuccessful()
        ->assertViewIs('public.contact')
        ->assertViewHas('openingHours', fn ($value) => $value instanceof OpeningHours)
        ->assertViewHas('weekdays', Weekdays::DAYS);
});

test('it displays business hours on the contact page', function () {
    (new BusinessHoursSeeder)->run();

    $response = $this->get(route('contact.index'));

    $response->assertSuccessful()
        ->assertSee('Openingstijden')
        ->assertSee(__('filament-business-hours::filament-business-hours.days.monday'))
        ->assertSee(__('filament-business-hours::filament-business-hours.days.saturday'));
});

test('it displays upcoming exceptional hours and vacation periods on the contact page', function () {
    $this->travelTo('2026-01-10 10:00:00');

    BusinessHours::query()->create([
        'key' => 'default',
        'timezone' => null,
        'overflow' => false,
        'hours' => [
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00'],
            'wednesday' => ['08:00-12:00'],
            'thursday' => ['08:00-12:00'],
            'friday' => ['08:00-12:00'],
            'saturday' => [],
            'sunday' => [],
        ],
        'exceptions' => [
            '2026-01-08 to 2026-01-12' => ['hours' => [], 'data' => 'Short closure'],
            '2026-01-13' => ['hours' => ['10:00-12:00'], 'data' => 'Today only'],
            '2026-01-15' => ['hours' => ['10:00-14:00'], 'data' => 'Inventory'],
            '2026-01-24' => ['hours' => ['09:00-10:00'], 'data' => 'Boundary'],
            '2026-01-25' => ['hours' => [], 'data' => 'Too far ahead'],
            '12-24' => ['hours' => [], 'data' => 'Christmas'],
            '2026-02-01 to 2026-02-07' => ['hours' => [], 'data' => 'Winter holiday'],
        ],
    ]);

    $response = $this->get(route('contact.index'));

    $response->assertSuccessful()
        ->assertSee('10:00 - 12:00')
        ->assertSee('Inventory')
        ->assertSee('Short closure')
        ->assertSee('08/01/2026')
        ->assertSee('12/01/2026')
        ->assertSee('Boundary')
        ->assertDontSee('Too far ahead')
        ->assertDontSee('Christmas')
        ->assertDontSee('Winter holiday')
        ->assertViewHas('specialOpeningHours', function (array $value): bool {
            return count($value) === 4
                && $value[0]['endDate']->format('d/m/Y') === '12/01/2026'
                && $value[3]['label'] === 'Boundary';
        });

    $this->travelBack();
});

test('it can store a contact message successfully', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->followingRedirects()->post(route('contact.store'), $data);

    $response->assertSuccessful()
        ->assertSee(__('Thank you for your message! We’ll get back to you as soon as possible.'));

    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class);
});

test('it can store a contact message with empty phone field successfully', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->followingRedirects()->post(route('contact.store'), $data);

    $response->assertSuccessful()
        ->assertSee(__('Thank you for your message! We’ll get back to you as soon as possible.'));

    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => null,
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class);
});

test('it fails validation when storing invalid contact message', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $response = $this->post(route('contact.store'));

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);
    expect(Message::count())->toBe(0);

    Mail::assertNothingQueued();
});

test('rejects request with honeypot', function () {
    Event::fake();
    Mail::fake();

    config()->set('honeypot.enabled', true);

    $data = [
        'my_first_name' => 'Jane', // This is the honeypot field and triggers spam detection when filled
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $response = $this->post(route('contact.store'), $data);

    expect($response->content())->toBeEmpty();

    Event::assertDispatched(SpamDetectedEvent::class);

    $this->assertDatabaseMissing('messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ]);

    Mail::assertNothingQueued();
});

test('it limits too many requests', function () {
    Mail::fake();

    config()->set('honeypot.enabled', true);

    // First 3 requests will succeed
    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe1',
        'email' => 'jane1@example.com',
        'phone' => '',
        'subject' => 'Inquiry1',
        'message' => 'Hello, I have a question1.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe1',
        'email' => 'jane1@example.com',
        'subject' => 'Inquiry1',
        'message' => 'Hello, I have a question1.',
    ]);

    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe2',
        'email' => 'jane2@example.com',
        'phone' => '',
        'subject' => 'Inquiry2',
        'message' => 'Hello, I have a question2.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe2',
        'email' => 'jane2@example.com',
        'subject' => 'Inquiry2',
        'message' => 'Hello, I have a question2.',
    ]);

    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe3',
        'email' => 'jane3@example.com',
        'phone' => '',
        'subject' => 'Inquiry3',
        'message' => 'Hello, I have a question3.',
    ]);
    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'name' => 'Jane Doe3',
        'email' => 'jane3@example.com',
        'subject' => 'Inquiry3',
        'message' => 'Hello, I have a question3.',
    ]);

    // 4th request will fail
    $response = $this->followingRedirects()->post(route('contact.store'), [
        'name' => 'Jane Doe4',
        'email' => 'jane4@example.com',
        'phone' => '',
        'subject' => 'Inquiry4',
        'message' => 'Hello, I have a question4.',
    ]);
    $response->assertTooManyRequests();
    $this->assertDatabaseMissing('messages', [
        'name' => 'Jane Doe4',
        'email' => 'jan43@example.com',
        'subject' => 'Inquiry4',
        'message' => 'Hello, I have a question4.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class, 3);
});
