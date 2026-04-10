<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Event;

it('registers event listeners for all three scheduler events', function (): void
{
    expect(Event::getListeners(ScheduledTaskStarting::class))
        ->not->toBeEmpty()
        ->and(Event::getListeners(ScheduledTaskFinished::class))
        ->not->toBeEmpty()
        ->and(Event::getListeners(ScheduledTaskFailed::class))
        ->not->toBeEmpty();
});

it('binds the command model to the container', function (): void
{
    expect(app(LaravelScheduledCommand::class))
        ->toBeInstanceOf(LaravelScheduledCommand::class);
});

it('binds the history model to the container', function (): void
{
    expect(app(LaravelScheduledCommandHistory::class))
        ->toBeInstanceOf(LaravelScheduledCommandHistory::class);
});

it('registers the prune artisan command', function (): void
{
    $this->artisan('schedule-log:prune')
        ->assertSuccessful();
});
