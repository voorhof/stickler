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

test('it caches business hours until the record is saved', function () {
    $this->travelTo('2026-01-10 10:00:00');

    $record = BusinessHours::query()->create([
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
            '2026-01-15' => ['hours' => [], 'data' => 'Inventory'],
        ],
    ]);

    $this->get(route('contact.index'))->assertSee('Inventory');

    BusinessHours::query()
        ->whereKey($record->getKey())
        ->toBase()
        ->update(['exceptions' => json_encode(['2026-01-15' => ['hours' => [], 'data' => 'Stocktake']])]);

    $this->get(route('contact.index'))
        ->assertSee('Inventory')
        ->assertDontSee('Stocktake');

    $record->update(['exceptions' => ['2026-01-15' => ['hours' => [], 'data' => 'Stocktake']]]);

    $this->get(route('contact.index'))
        ->assertSee('Stocktake')
        ->assertDontSee('Inventory');

    $this->travelBack();
});

function createContactBusinessHours(string $key, array $exceptions = [], ?string $timezone = null): BusinessHours
{
    return BusinessHours::query()->create([
        'key' => $key,
        'timezone' => $timezone,
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
        'exceptions' => $exceptions,
    ]);
}

test('it passes no special opening hours when there is no business hours record', function () {
    $this->get(route('contact.index'))
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => $value === []);
});

test('it uses the business hours timezone to determine the upcoming window', function () {
    $this->travelTo('2026-01-11 02:00:00');

    createContactBusinessHours('default', [
        '2026-01-10' => ['hours' => [], 'data' => 'Evening closure'],
    ], 'America/New_York');

    $this->get(route('contact.index'))
        ->assertSee('Evening closure')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => count($value) === 1
            && $value[0]['date']->format('d/m/Y') === '10/01/2026');

    $this->travelBack();
});

test('it excludes single dates and periods that have already passed', function () {
    $this->travelTo('2026-01-10 10:00:00');

    createContactBusinessHours('default', [
        '2026-01-07' => ['hours' => [], 'data' => 'Past single'],
        '2026-01-01 to 2026-01-05' => ['hours' => [], 'data' => 'Past holiday'],
    ]);

    $this->get(route('contact.index'))
        ->assertDontSee('Past single')
        ->assertDontSee('Past holiday')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => $value === []);

    $this->travelBack();
});

test('it shows a recurring exception inside the window with the recurring flag', function () {
    $this->travelTo('2026-01-10 10:00:00');

    createContactBusinessHours('default', [
        '01-12' => ['hours' => ['09:00-10:00'], 'data' => 'Annual check'],
    ]);

    $this->get(route('contact.index'))
        ->assertSee('Annual check')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => count($value) === 1
            && $value[0]['date']->format('d/m/Y') === '12/01/2026'
            && $value[0]['endDate'] === null
            && $value[0]['hours'] === ['09:00-10:00']
            && $value[0]['label'] === 'Annual check'
            && $value[0]['recurring'] === true);

    $this->travelBack();
});

test('it moves a recurring exception that already passed this year into next year', function () {
    $this->travelTo('2026-12-28 10:00:00');

    createContactBusinessHours('default', [
        '01-02' => ['hours' => [], 'data' => 'New year check'],
    ]);

    $this->get(route('contact.index'))
        ->assertSee('New year check')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => count($value) === 1
            && $value[0]['date']->format('d/m/Y') === '02/01/2027'
            && $value[0]['recurring'] === true);

    $this->travelBack();
});

test('it excludes a recurring exception whose next occurrence is outside the window', function () {
    $this->travelTo('2026-01-10 10:00:00');

    createContactBusinessHours('default', [
        '01-05' => ['hours' => [], 'data' => 'Past annual'],
    ]);

    $this->get(route('contact.index'))
        ->assertDontSee('Past annual')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => $value === []);

    $this->travelBack();
});

test('it exposes the date, hours, label and non recurring flag of a single date exception', function () {
    $this->travelTo('2026-01-10 10:00:00');

    createContactBusinessHours('default', [
        '2026-01-13' => ['hours' => ['10:00-12:00'], 'data' => 'Today only'],
    ]);

    $this->get(route('contact.index'))
        ->assertSee('Today only')
        ->assertViewHas('specialOpeningHours', fn (array $value): bool => count($value) === 1
            && $value[0]['date']->format('Y-m-d') === '2026-01-13'
            && $value[0]['endDate'] === null
            && $value[0]['hours'] === ['10:00-12:00']
            && $value[0]['label'] === 'Today only'
            && $value[0]['recurring'] === false);

    $this->travelBack();
});

test('it uses the configured filament business hours record', function () {
    $this->travelTo('2026-01-10 10:00:00');

    config()->set('filament-business-hours.record', 'seasonal');

    createContactBusinessHours('seasonal', [
        '2026-01-15' => ['hours' => [], 'data' => 'Seasonal closure'],
    ]);

    $this->get(route('contact.index'))
        ->assertSee('Seasonal closure')
        ->assertViewHas('openingHours', fn ($value) => $value instanceof OpeningHours);

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

test('it does not store a duplicate contact message sent within the duplicate window', function () {
    Mail::fake();

    config()->set('honeypot.enabled', false);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $this->post(route('contact.store'), $data)->assertRedirect(route('contact.index'));
    $this->post(route('contact.store'), $data)->assertRedirect(route('contact.index'));

    expect(Message::count())->toBe(1);

    Mail::assertQueuedCount(1);
});

test('it reports an error and stores nothing when the message cannot be queued', function () {
    config()->set('honeypot.enabled', false);

    Mail::shouldReceive('queue')
        ->once()
        ->andThrow(new RuntimeException('Mail transport unavailable'));

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '123456789',
        'subject' => 'Inquiry',
        'message' => 'Hello, I have a question.',
    ];

    $this->post(route('contact.store'), $data)
        ->assertRedirect(route('contact.index'))
        ->assertSessionHas('error');

    expect(Message::count())->toBe(0);
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
        'email' => 'jane4@example.com',
        'subject' => 'Inquiry4',
        'message' => 'Hello, I have a question4.',
    ]);

    Mail::assertQueued(ContactMessageReceived::class, 3);
});
