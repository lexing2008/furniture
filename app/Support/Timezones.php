<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

/**
 * IANA timezones for the admin region form, sorted by the current UTC offset.
 */
class Timezones
{
    /**
     * @return array<int, array{value: string, offset: int, label: string}>
     */
    public static function options(): array
    {
        $now = new DateTimeImmutable('now');
        $options = [];

        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            $offset = (new DateTimeZone($identifier))->getOffset($now);

            $options[] = [
                'value' => $identifier,
                'offset' => $offset,
                'label' => self::label($offset, $identifier),
            ];
        }

        usort($options, fn (array $a, array $b): int => [$a['offset'], $a['value']] <=> [$b['offset'], $b['value']]);

        return $options;
    }

    /**
     * "(UTC+03:00) Europe/Minsk"
     */
    public static function label(int $offset, string $identifier): string
    {
        $sign = $offset < 0 ? '-' : '+';
        $absolute = abs($offset);

        return sprintf(
            '(UTC%s%02d:%02d) %s',
            $sign,
            intdiv($absolute, 3600),
            intdiv($absolute % 3600, 60),
            $identifier,
        );
    }
}
