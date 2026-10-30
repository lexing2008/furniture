<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyScheduleRequest;
use App\Models\CompanySchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Weekly working hours of the company and its non-working days.
 */
class CompanyScheduleController extends Controller
{
    use ResolvesCompany;

    /** 1 = Monday, ..., 7 = Sunday (ISO-8601) */
    private const DAYS = [1, 2, 3, 4, 5, 6, 7];

    public function edit(Request $request): Response
    {
        $company = $this->company($request, ['schedules', 'scheduleExceptions', 'region']);
        $schedules = $company->schedules->keyBy('day_of_week');
        $timezone = $company->region?->getTimezone();
        $today = now($timezone)->toDateString();

        return Inertia::render('Company/Schedule', [
            'schedule' => collect(self::DAYS)
                ->map(fn (int $day) => $this->dayData($day, $schedules->get($day)))
                ->values(),
            'exceptions' => $company->scheduleExceptions->map(fn ($exception) => [
                'date' => (string) $exception->date,
                'name' => $exception->name ?? '',
                'is_expired' => (string) $exception->date < $today,
            ])->values(),
            'timezone' => $timezone,
            'today' => $today,
        ]);
    }

    public function update(CompanyScheduleRequest $request): RedirectResponse
    {
        $company = $this->company($request);

        DB::transaction(function () use ($request, $company) {
            $company->schedules()->delete();
            $company->schedules()->createMany($request->scheduleData());

            $company->scheduleExceptions()->delete();
            $company->scheduleExceptions()->createMany($request->exceptionsData());
        });

        return back()->with('success', __('График работы сохранён.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function dayData(int $day, ?CompanySchedule $schedule): array
    {
        // The default week: Monday-Friday 09:00-18:00, weekends are days off
        $isClosed = $schedule?->is_closed ?? in_array($day, [6, 7], true);
        $hasBreak = ! $isClosed && ($schedule?->has_break ?? false);

        return [
            'day_of_week' => $day,
            'is_closed' => $isClosed,
            'open_at' => $isClosed ? '09:00' : $this->time($schedule?->open_at, '09:00'),
            'close_at' => $isClosed ? '18:00' : $this->time($schedule?->close_at, '18:00'),
            'has_break' => $hasBreak,
            'break_start_at' => $hasBreak ? $this->time($schedule?->break_start_at, '') : '',
            'break_end_at' => $hasBreak ? $this->time($schedule?->break_end_at, '') : '',
        ];
    }

    private function time(?string $value, string $default): string
    {
        return $value === null || $value === '' ? $default : substr($value, 0, 5);
    }
}
