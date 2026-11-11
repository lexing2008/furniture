@php
    $now = now($company->timezone());
    $todayDayOfWeek = $now->dayOfWeekIso;
    $todayDate = $now->toDateString();
    $todaySchedule = $company->schedules->firstWhere('day_of_week', $todayDayOfWeek);
    $isTodayException = $company->scheduleExceptions->contains(
        fn ($exception) => (string) $exception->date === $todayDate,
    );
    $toMinutes = function (?string $time): ?int {
        if ($time === null || $time === '') {
            return null;
        }

        return ((int) substr($time, 0, 2)) * 60 + ((int) substr($time, 3, 2));
    };
    $nowMinutes = $now->hour * 60 + $now->minute;
    $openMinutes = $toMinutes($todaySchedule?->open_at);
    $closeMinutes = $toMinutes($todaySchedule?->close_at);

    if ($closeMinutes === 0) {
        $closeMinutes = 24 * 60;
    }
    $breakStartMinutes = $todaySchedule?->has_break ? $toMinutes($todaySchedule->break_start_at) : null;
    $breakEndMinutes = $todaySchedule?->has_break ? $toMinutes($todaySchedule->break_end_at) : null;
    $isDayOff = $isTodayException || $todaySchedule === null || $todaySchedule->is_closed;
    $isLunch = ! $isDayOff
        && $todaySchedule?->has_break
        && $breakStartMinutes !== null
        && $breakEndMinutes !== null
        && $nowMinutes >= $breakStartMinutes
        && $nowMinutes < $breakEndMinutes;
    $isOpen = ! $isDayOff
        && ! $isLunch
        && $openMinutes !== null
        && $closeMinutes !== null
        && $nowMinutes >= $openMinutes
        && $nowMinutes < $closeMinutes;
    $statusType = $isDayOff ? 'day_off' : ($isLunch ? 'lunch' : ($isOpen ? 'open' : 'closed'));
    $statusText = match ($statusType) {
        'open' => __('Открыто'),
        'lunch' => __('Сейчас обед, откроемся в :time', ['time' => substr((string) $todaySchedule?->break_end_at, 0, 5)]),
        'day_off' => __('Закрыто. Нерабочий день.'),
        default => __('Закрыто. Нерабочее время'),
    };
    $scheduleDayNames = [
        1 => __('Понедельник'),
        2 => __('Вторник'),
        3 => __('Среда'),
        4 => __('Четверг'),
        5 => __('Пятница'),
        6 => __('Суббота'),
        7 => __('Воскресенье'),
    ];
@endphp

<div
    @class([
        'mb-4 flex items-center gap-2.5 rounded-xl border px-3.5 py-3',
        'border-green-first/15 bg-green-first/5' => $statusType === 'open',
        'border-amber-100 bg-amber-50/70' => $statusType === 'lunch',
        'border-red-100 bg-red-50/60' => $statusType === 'day_off' || $statusType === 'closed',
    ])
>
    <span
        @class([
            'flex h-5 w-5 shrink-0 items-center justify-center rounded-full',
            'bg-green-first text-white' => $statusType === 'open',
            'bg-amber-400 text-white' => $statusType === 'lunch',
            'bg-red-400 text-white' => $statusType === 'day_off' || $statusType === 'closed',
        ])
    >
        @if($statusType === 'open')
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        @elseif($statusType === 'lunch')
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        @endif
    </span>
    <p
        @class([
            'text-sm font-semibold',
            'text-green-first' => $statusType === 'open',
            'text-amber-700' => $statusType === 'lunch',
            'text-red-600' => $statusType === 'day_off' || $statusType === 'closed',
        ])
    >{{ $statusText }}</p>
</div>

<div class="divide-y divide-gray-100 rounded-xl border border-gray-100 bg-gray-50/50 px-3">
    @foreach($company->schedules as $schedule)
        <div class="flex items-start justify-between gap-4 py-2.5 text-sm">
            <span @class(['text-gray-500', 'font-semibold text-green-first' => $schedule->day_of_week === $todayDayOfWeek])>{{ $scheduleDayNames[$schedule->day_of_week] ?? '' }}</span>
            <span class="text-right font-medium text-gray-800">
                @if($schedule->is_closed)
                    {{ __('Выходной') }}
                @else
                    {{ substr($schedule->open_at, 0, 5) }}–{{ $schedule->close_at === '00:00' ? '24:00' : substr($schedule->close_at, 0, 5) }}
                    @if($schedule->has_break)
                        <span class="block text-[11px] font-normal text-gray-400">{{ __('Перерыв') }}: {{ substr($schedule->break_start_at, 0, 5) }}–{{ substr($schedule->break_end_at, 0, 5) }}</span>
                    @endif
                @endif
            </span>
        </div>
    @endforeach
</div>

@php
    $today = now($company->timezone())->toDateString();
    $upcomingExceptions = $company->scheduleExceptions
        ->filter(fn ($exception) => (string) $exception->date >= $today);
@endphp

@if($upcomingExceptions->isNotEmpty())
    <div class="mt-4 border-t border-gray-100 pt-3">
        <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400">{{ __('Нерабочие дни') }}</p>
        <div class="space-y-1.5">
            @foreach($upcomingExceptions as $exception)
                <div class="flex items-start justify-between gap-4 text-sm">
                    <span class="text-gray-500">{{ \Illuminate\Support\Carbon::parse($exception->date)->format('d.m.Y') }}</span>
                    <span class="text-right font-medium text-gray-800">{{ $exception->name ?: __('Выходной') }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
