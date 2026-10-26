<?php

namespace Tests\Unit;

use App\Support\Timezones;
use PHPUnit\Framework\TestCase;

class TimezonesTest extends TestCase
{
    public function test_options_are_sorted_by_offset(): void
    {
        $options = Timezones::options();
        $offsets = array_column($options, 'offset');

        $sorted = $offsets;
        sort($sorted);

        $this->assertNotEmpty($options);
        $this->assertSame($sorted, $offsets);
    }

    public function test_known_timezones_have_offset_labels(): void
    {
        $labels = array_column(Timezones::options(), 'label', 'value');

        $this->assertSame('(UTC+03:00) Europe/Minsk', $labels['Europe/Minsk']);
        $this->assertSame('(UTC+07:00) Asia/Bangkok', $labels['Asia/Bangkok']);
        $this->assertSame('(UTC-03:00) America/Argentina/Buenos_Aires', $labels['America/Argentina/Buenos_Aires']);
    }
}
