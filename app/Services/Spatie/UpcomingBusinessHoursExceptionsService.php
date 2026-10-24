<?php

namespace App\Services\Spatie;

use Carbon\CarbonImmutable;
use LaBoiteACode\FilamentBusinessHours\Support\ExceptionEntry;
use LaBoiteACode\FilamentBusinessHours\Support\ExceptionType;

class UpcomingBusinessHoursExceptionsService
{
    /**
     * @param  array<array-key, mixed>  $exceptions
     * @return list<array{date: CarbonImmutable, endDate: CarbonImmutable|null, hours: list<string>, label: string|null, recurring: bool}>
     */
    public function resolve(array $exceptions, CarbonImmutable $today, int $weeksAhead = 2): array
    {
        ['entries' => $entries] = ExceptionEntry::parseMap($exceptions);
        $windowStart = $today->startOfDay();
        $windowEnd = $windowStart->addWeeks($weeksAhead);
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
}
