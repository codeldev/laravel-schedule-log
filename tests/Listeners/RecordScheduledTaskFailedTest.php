<?php

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as SchedulingEvent;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void
{
    $this->task              = Mockery::mock(SchedulingEvent::class);
    $this->task->command     = "'artisan' inspire";
    $this->task->description = 'Inspire the developer';
    $this->task->exitCode    = 1;
    $this->task->output      = tempnam(sys_get_temp_dir(), 'schedule_test_');
    $this->task->shouldReceive('mutexName')->andReturn('framework/schedule-test-mutex');

    Event::dispatch(new ScheduledTaskStarting($this->task));
});

afterEach(function (): void
{
    if (file_exists($this->task->output))
    {
        unlink($this->task->output);
    }
});

it('marks a task as failed with the exception message', function (): void
{
    $exception = new RuntimeException('Command blew up');

    Event::dispatch(new ScheduledTaskFailed($this->task, $exception));

    expect(LaravelScheduledCommandHistory::first())
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::FAILED)
        ->error
        ->toBe('Command blew up')
        ->completed_at
        ->not->toBeNull()
        ->duration_ms
        ->not->toBeNull();
});

it('cleans up the cache key', function (): void
{
    Event::dispatch(new ScheduledTaskFailed($this->task, new RuntimeException('fail')));

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'framework/schedule-test-mutex'))
        ->toBeNull();
});

it('preserves duration and completed_at from finished listener in double-fire scenario', function (): void
{
    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 3.25));
    Event::dispatch(new ScheduledTaskFailed($this->task, new RuntimeException('Exit code 1')));

    expect(LaravelScheduledCommandHistory::first())
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::FAILED)
        ->error
        ->toBe('Exit code 1')
        ->duration_ms
        ->toBe(3250);
});

it('reports the exception when the listener fails internally', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(
        segments: 'framework/schedule-test-mutex'
    );

    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
        value: 'non-existent-uuid',
        expires: now()->addHour(),
        segments: 'framework/schedule-test-mutex',
    );

    Event::dispatch(new ScheduledTaskFailed($this->task, new RuntimeException('fail')));

    expect(true)
        ->toBeTrue();
});

it('does nothing when the cache key is missing', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(
        segments: 'framework/schedule-test-mutex'
    );

    $history        = LaravelScheduledCommandHistory::first();
    $originalStatus = $history->status;

    Event::dispatch(new ScheduledTaskFailed($this->task, new RuntimeException('fail')));

    expect($history->fresh()->status)
        ->toBe($originalStatus);
});
