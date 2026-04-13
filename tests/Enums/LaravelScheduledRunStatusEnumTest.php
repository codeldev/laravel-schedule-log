<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;

it('has the correct backing values', function (): void
{
    expect(LaravelScheduledRunStatusEnum::RUNNING->value)
        ->toBe(1)
        ->and(LaravelScheduledRunStatusEnum::SUCCEEDED->value)
        ->toBe(2)
        ->and(LaravelScheduledRunStatusEnum::FAILED->value)
        ->toBe(3);
});

it('returns human readable labels', function (): void
{
    expect(LaravelScheduledRunStatusEnum::RUNNING->label())
        ->toBe('Running')
        ->and(LaravelScheduledRunStatusEnum::SUCCEEDED->label())
        ->toBe('Succeeded')
        ->and(LaravelScheduledRunStatusEnum::FAILED->label())
        ->toBe('Failed');
});
