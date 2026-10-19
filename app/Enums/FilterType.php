<?php

namespace App\Enums;

enum FilterType: string
{
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case INPUT_INT = 'input_int';
    case INPUT_FLOAT = 'input_float';

    public function label(): string
    {
        return match ($this) {
            self::CHECKBOX => 'Чекбоксы (несколько значений)',
            self::RADIO => 'Радиокнопки (одно значение)',
            self::INPUT_INT => 'Целое число',
            self::INPUT_FLOAT => 'Дробное число',
        };
    }

    /**
     * Only checkbox and radio offer a list of values; numbers are typed in by the visitor.
     */
    public function hasVariants(): bool
    {
        return in_array($this, [self::CHECKBOX, self::RADIO], true);
    }

    /**
     * Options for <select> on the frontend.
     *
     * @return array<int, array{value: string, label: string, has_variants: bool}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label(), 'has_variants' => $type->hasVariants()],
            self::cases(),
        );
    }
}
