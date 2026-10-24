<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\Message;
use App\Services\Spatie\BusinessHoursCacheService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use LaBoiteACode\FilamentBusinessHours\Support\ExceptionEntry;
use LaBoiteACode\FilamentBusinessHours\Support\ExceptionType;
use LaBoiteACode\FilamentBusinessHours\Support\Weekdays;
use Laravel\Head\Facades\Head;
use Spatie\OpeningHours\OpeningHours;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function index(BusinessHoursCacheService $businessHoursCache): View
    {
        Head::title('Contact')
            ->description('Contact details '.config('app.name'));

        $businessHours = $businessHoursCache->resolve(config('filament-business-hours.record', 'default'));
        $definition = $businessHours['definition'];
        $today = CarbonImmutable::now($definition['timezone'] ?? config('app.timezone'));

        return view('public.contact', [
            'openingHours' => $businessHours['exists'] ? OpeningHours::create($definition) : null,
            'specialOpeningHours' => $businessHours['exists']
                ? $this->upcomingExceptions($definition['exceptions'], $today)
                : [],
            'today' => $today,
            'weekdays' => Weekdays::DAYS,
        ]);
    }

    /**
     * @param  array<array-key, mixed>  $exceptions
     * @return list<array{date: CarbonImmutable, endDate: CarbonImmutable|null, hours: list<string>, label: string|null, recurring: bool}>
     */
    private function upcomingExceptions(array $exceptions, CarbonImmutable $today): array
    {
        ['entries' => $entries] = ExceptionEntry::parseMap($exceptions);
        $windowStart = $today->startOfDay();
        $windowEnd = $windowStart->addWeeks(2);
        $parseDate = static fn (string $date): CarbonImmutable => CarbonImmutable::createFromFormat('!Y-m-d', $date, $windowStart->getTimezone());
        $upcomingExceptions = [];

        foreach ($entries as $entry) {
            $isRecurring = $entry->type === ExceptionType::Recurring;
            $date = match ($entry->type) {
                ExceptionType::Date => $parseDate((string) $entry->date),
                ExceptionType::Recurring => $parseDate($windowStart->year.'-'.$entry->date),
                ExceptionType::Period => $parseDate((string) $entry->from),
            };
            $endDate = $entry->type === ExceptionType::Period ? $parseDate((string) $entry->until) : null;

            if ($isRecurring && $date->lt($windowStart)) {
                $date = $date->addYear();
            }

            if ($date->gt($windowEnd) || ($endDate ?? $date)->lt($windowStart)) {
                continue;
            }

            $upcomingExceptions[] = [
                'date' => $date,
                'endDate' => $endDate,
                'hours' => $entry->hours,
                'label' => $entry->label,
                'recurring' => $isRecurring,
            ];
        }

        usort(
            $upcomingExceptions,
            static fn (array $first, array $second): int => $first['date'] <=> $second['date'],
        );

        return $upcomingExceptions;
    }

    /**
     * Store a newly created contact message in storage.
     */
    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $message = Message::create($request->validated());

        // Send mail to the admin
        Mail::queue(new ContactMessageReceived($message));

        return redirect()
            ->route('contact.index')
            ->with('success', __('Thank you for your message! We’ll get back to you as soon as possible.'));
    }
}
