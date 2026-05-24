<?php

namespace App\Modules\Analytics\Application\DTOs;

use Carbon\Carbon;

readonly class DateRangeDTO
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
    ) {}

    public static function lastDays(int $days): self
    {
        return new self(
            from: now()->subDays($days - 1)->startOfDay(),
            to:   now()->endOfDay(),
        );
    }

    public static function fromStrings(?string $from, ?string $to, int $defaultDays = 30): self
    {
        return new self(
            from: $from ? Carbon::parse($from)->startOfDay() : now()->subDays($defaultDays - 1)->startOfDay(),
            to:   $to   ? Carbon::parse($to)->endOfDay()     : now()->endOfDay(),
        );
    }
}
