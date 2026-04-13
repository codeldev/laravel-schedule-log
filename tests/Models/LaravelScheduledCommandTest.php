<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;

it('uses the configured table name', function (): void
{
    expect((new LaravelScheduledCommand)->getTable())
        ->toBe(config('schedule-log.tables.commands'));
});

it('has uuid primary keys', function (): void
{
    $command = LaravelScheduledCommand::factory()
        ->create();

    expect($command->id)
        ->toBeString()
        ->and(mb_strlen($command->id))
        ->toBe(36);
});

it('has a history relationship', function (): void
{
    $command = LaravelScheduledCommand::factory()
        ->create();

    LaravelScheduledCommandHistory::factory()
        ->for($command, 'scheduledCommand')
        ->count(3)
        ->create();

    expect($command->history)
        ->toHaveCount(3)
        ->each
        ->toBeInstanceOf(LaravelScheduledCommandHistory::class);
});

it('casts timestamps to immutable datetimes', function (): void
{
    $command = LaravelScheduledCommand::factory()->create();

    expect($command->created_at)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($command->updated_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});
