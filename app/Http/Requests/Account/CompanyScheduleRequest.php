<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Weekly working hours of a company and its non-working days.
 *
 * schedule: [{day_of_week: 1..7, is_closed, open_at, close_at, has_break, break_start_at, break_end_at}, ...]
 * exceptions: [{date: Y-m-d, name: ?string}, ...]
 */
class CompanyScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $schedule = collect($this->input('schedule', []))
            ->map(function ($day) {
                $day = is_array($day) ? $day : [];

                return [
                    'day_of_week' => (int) ($day['day_of_week'] ?? 0),
                    'is_closed' => filter_var($day['is_closed'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'has_break' => filter_var($day['has_break'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'open_at' => trim((string) ($day['open_at'] ?? '')),
                    'close_at' => trim((string) ($day['close_at'] ?? '')),
                    'break_start_at' => trim((string) ($day['break_start_at'] ?? '')),
                    'break_end_at' => trim((string) ($day['break_end_at'] ?? '')),
                ];
            })
            ->sortBy('day_of_week')
            ->values()
            ->all();

        $exceptions = collect($this->input('exceptions', []))
            ->map(function ($exception) {
                $exception = is_array($exception) ? $exception : [];

                return [
                    'date' => trim((string) ($exception['date'] ?? '')),
                    'name' => trim((string) ($exception['name'] ?? '')) ?: null,
                ];
            })
            // A blank new row is not an exception
            ->reject(fn (array $exception) => $exception['date'] === '' && $exception['name'] === null)
            ->sortBy('date')
            ->values()
            ->all();

        $this->merge([
            'schedule' => $schedule,
            'exceptions' => $exceptions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'schedule' => ['required', 'array', 'size:7'],
            'schedule.*.day_of_week' => ['required', 'integer', 'between:1,7', 'distinct'],
            'schedule.*.is_closed' => ['required', 'boolean'],
            'schedule.*.has_break' => ['required', 'boolean'],
            'exceptions' => ['array'],
            'exceptions.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
            'exceptions.*.name' => ['nullable', 'string', 'max:255'],
        ];

        // Times are required only for working days, the break — only when it is enabled
        foreach ($this->input('schedule', []) as $index => $day) {
            if ($day['is_closed']) {
                continue;
            }

            $rules["schedule.{$index}.open_at"] = ['required', 'date_format:H:i'];
            $rules["schedule.{$index}.close_at"] = ['required', 'date_format:H:i'];

            if ($day['has_break']) {
                $rules["schedule.{$index}.break_start_at"] = ['required', 'date_format:H:i'];
                $rules["schedule.{$index}.break_end_at"] = ['required', 'date_format:H:i'];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ($this->input('schedule', []) as $index => $day) {
                    if ($day['is_closed']) {
                        continue;
                    }

                    $open = $day['open_at'];
                    $close = $day['close_at'];

                    if ($this->isTime($open) && $this->isTime($close) && $close <= $open) {
                        $validator->errors()->add("schedule.{$index}.close_at", __('Время закрытия должно быть позже времени открытия.'));
                    }

                    if (! $day['has_break']) {
                        continue;
                    }

                    $breakStart = $day['break_start_at'];
                    $breakEnd = $day['break_end_at'];

                    if ($this->isTime($breakStart) && $this->isTime($breakEnd) && $breakEnd <= $breakStart) {
                        $validator->errors()->add("schedule.{$index}.break_end_at", __('Конец перерыва должен быть позже его начала.'));
                    }

                    if ($this->isTime($open) && $this->isTime($breakStart) && $breakStart < $open) {
                        $validator->errors()->add("schedule.{$index}.break_start_at", __('Перерыв должен быть внутри рабочего времени.'));
                    }

                    if ($this->isTime($close) && $this->isTime($breakEnd) && $breakEnd > $close) {
                        $validator->errors()->add("schedule.{$index}.break_end_at", __('Перерыв должен быть внутри рабочего времени.'));
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('Заполните поле «:attribute».'),
            'schedule.required' => __('Заполните график работы.'),
            'schedule.size' => __('График должен содержать 7 дней.'),
            'schedule.*.day_of_week.between' => __('Некорректный день недели.'),
            'schedule.*.day_of_week.distinct' => __('День недели указан дважды.'),
            'schedule.*.open_at.date_format' => __('Укажите время в формате ЧЧ:ММ.'),
            'schedule.*.close_at.date_format' => __('Укажите время в формате ЧЧ:ММ.'),
            'schedule.*.break_start_at.date_format' => __('Укажите время в формате ЧЧ:ММ.'),
            'schedule.*.break_end_at.date_format' => __('Укажите время в формате ЧЧ:ММ.'),
            'exceptions.*.date.date_format' => __('Укажите дату в формате ГГГГ-ММ-ДД.'),
            'exceptions.*.date.distinct' => __('Эта дата указана дважды.'),
            'string' => __('Поле «:attribute» должно быть текстом.'),
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'schedule.*.day_of_week' => __('день недели'),
            'schedule.*.open_at' => __('время открытия'),
            'schedule.*.close_at' => __('время закрытия'),
            'schedule.*.break_start_at' => __('начало перерыва'),
            'schedule.*.break_end_at' => __('конец перерыва'),
            'exceptions.*.date' => __('дата'),
            'exceptions.*.name' => __('название'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function scheduleData(): array
    {
        return collect($this->validated('schedule'))
            ->map(function (array $day) {
                $isClosed = (bool) $day['is_closed'];
                $hasBreak = ! $isClosed && (bool) $day['has_break'];

                return [
                    'day_of_week' => (int) $day['day_of_week'],
                    // The columns are not nullable, "is_closed" is the source of truth for a day off
                    'open_at' => $isClosed ? '00:00' : $day['open_at'],
                    'close_at' => $isClosed ? '00:00' : $day['close_at'],
                    'has_break' => $hasBreak,
                    'break_start_at' => $hasBreak ? $day['break_start_at'] : null,
                    'break_end_at' => $hasBreak ? $day['break_end_at'] : null,
                    'is_closed' => $isClosed,
                ];
            })
            ->sortBy('day_of_week')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{date: string, name: ?string}>
     */
    public function exceptionsData(): array
    {
        return collect($this->validated('exceptions', []))
            ->map(fn (array $exception) => [
                'date' => $exception['date'],
                'name' => $exception['name'] ?? null,
            ])
            ->sortBy('date')
            ->values()
            ->all();
    }

    private function isTime(string $value): bool
    {
        return preg_match('/^\d{2}:\d{2}$/', $value) === 1;
    }
}
