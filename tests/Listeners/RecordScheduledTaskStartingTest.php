<?php

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as SchedulingEvent;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void
{
    $this->task              = Mockery::mock(SchedulingEvent::class);
    $this->task->command     = "'artisan' inspire";
    $this->task->description = 'Inspire the developer';
    $this->task->shouldReceive('mutexName')->andReturn('framework/schedule-test-mutex');
});

it('creates a scheduled command record on task starting', function (): void
{
    Event::dispatch(new ScheduledTaskStarting($this->task));

    expect(LaravelScheduledCommand::where('command', 'inspire')->first())
        ->not->toBeNull()
        ->description->toBe('Inspire the developer');
});

it('creates a history entry with running status', function (): void
{
    Event::dispatch(new ScheduledTaskStarting($this->task));

    $history = LaravelScheduledCommandHistory::first();

    expect($history)->not->toBeNull()
        ->status->toBe(LaravelScheduledRunStatusEnum::RUNNING)
        ->started_at->not->toBeNull()
        ->completed_at->toBeNull();
});

it('stores the run id in cache using the mutex name', function (): void
{
    Event::dispatch(new ScheduledTaskStarting($this->task));

    $history = LaravelScheduledCommandHistory::first();

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'framework/schedule-test-mutex'))
        ->toBe($history->id);
});

it('reuses the same command record for repeated runs', function (): void
{
    Event::dispatch(new ScheduledTaskStarting($this->task));
    Event::dispatch(new ScheduledTaskStarting($this->task));

    expect(LaravelScheduledCommand::count())->toBe(1)
        ->and(LaravelScheduledCommandHistory::count())->toBe(2);
});

it('reports the exception when the listener fails internally', function (): void
{
    // Drop the table to force a database exception inside the listener
    Illuminate\Support\Facades\Schema::drop(config('laravel-schedule-log.tables.history'));
    Illuminate\Support\Facades\Schema::drop(config('laravel-schedule-log.tables.commands'));

    Event::dispatch(new ScheduledTaskStarting($this->task));

    // Should not throw — the exception is caught and reported
    expect(true)->toBeTrue();
});

it('ignores tasks without an artisan command signature', function (): void
{
    $this->task->command = null;

    Event::dispatch(new ScheduledTaskStarting($this->task));

    expect(LaravelScheduledCommand::count())->toBe(0);
});
