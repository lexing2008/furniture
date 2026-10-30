@php
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

<div class="divide-y divide-gray-100 rounded-xl border border-gray-100 bg-gray-50/50 px-3">
    @foreach($company->schedules as $schedule)
        <div class="flex items-start justify-between gap-4 py-2.5 text-sm">
            <span class="text-gray-500">{{ $scheduleDayNames[$schedule->day_of_week] ?? '' }}</span>
            <span class="text-right font-medium text-gray-800">
                @if($schedule->is_closed)
                    {{ __('Выходной') }}
                @else
                    {{ substr($schedule->open_at, 0, 5) }}–{{ substr($schedule->close_at, 0, 5) }}
                    @if($schedule->has_break)
                        <span class="block text-[11px] font-normal text-gray-400">{{ __('Перерыв') }}: {{ substr($schedule->break_start_at, 0, 5) }}–{{ substr($schedule->break_end_at, 0, 5) }}</span>
                    @endif
                @endif
            </span>
        </div>
    @endforeach
</div>

@if($company->scheduleExceptions->isNotEmpty())
    <div class="mt-4 border-t border-gray-100 pt-3">
        <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400">{{ __('Исключения') }}</p>
        <div class="space-y-1.5">
            @foreach($company->scheduleExceptions as $exception)
                <div class="flex items-start justify-between gap-4 text-sm">
                    <span class="text-gray-500">{{ \Illuminate\Support\Carbon::parse($exception->date)->format('d.m.Y') }}</span>
                    <span class="text-right font-medium text-gray-800">{{ $exception->name ?: __('Выходной') }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
