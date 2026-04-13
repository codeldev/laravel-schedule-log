<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;

it('uses the configured table name', function (): void
{
    expect((new LaravelScheduledCommandHistory)->getTable())
        ->toBe(config('schedule-log.tables.history'));
});

it('has uuid primary keys', function (): void
{
    $history = LaravelScheduledCommandHistory::factory()
        ->create();

    expect($history->id)
        ->toBeString()
        ->and(mb_strlen($history->id))
        ->toBe(36);
});

it('belongs to a scheduled command', function (): void
{
    $history = LaravelScheduledCommandHistory::factory()
        ->create();

    expect($history->scheduledCommand)
        ->toBeInstanceOf(LaravelScheduledCommand::class);
});

it('casts status to the run status enum', function (): void
{
    $history = LaravelScheduledCommandHistory::factory()->create([
        'status' => LaravelScheduledRunStatusEnum::SUCCEEDED,
    ]);

    expect($history->status)
        ->toBe(LaravelScheduledRunStatusEnum::SUCCEEDED);
});

it('casts timestamps to immutable datetimes', function (): void
{
    $history = LaravelScheduledCommandHistory::factory()
        ->create();

    expect($history->started_at)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($history->completed_at)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($history->created_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

it('casts duration_ms to integer', function (): void
{
    $history = LaravelScheduledCommandHistory::factory()
        ->create(['duration_ms' => 1500]);

    expect($history->duration_ms)
        ->toBeInt()
        ->toBe(1500);
});
