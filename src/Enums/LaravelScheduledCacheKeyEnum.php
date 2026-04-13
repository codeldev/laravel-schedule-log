<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Enums;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

enum LaravelScheduledCacheKeyEnum: string
{
    case SCHEDULED_RUN  = 'scheduled:run';

    public function get(string ...$segments): mixed
    {
        return Cache::get($this->with(...$segments));
    }

    public function pull(string ...$segments): mixed
    {
        return Cache::pull($this->with(...$segments));
    }

    public function put(mixed $value, int | CarbonInterface $expires, string ...$segments): void
    {
        Cache::put($this->with(...$segments), $value, $expires);
    }

    private function with(string ...$segments): string
    {
        return implode(':', [$this->value, ...$segments]);
    }
}
