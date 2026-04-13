<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Enums;

enum LaravelScheduledRunStatusEnum: int
{
    case RUNNING   = 1;
    case SUCCEEDED = 2;
    case FAILED    = 3;

    public function label(): string
    {
        return match ($this)
        {
            self::RUNNING   => 'Running',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED    => 'Failed',
        };
    }
}
