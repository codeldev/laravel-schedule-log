<?php

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as SchedulingEvent;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void
{
    $this->task              = Mockery::mock(SchedulingEvent::class);
    $this->task->command     = "'artisan' inspire";
    $this->task->description = 'Inspire the developer';
    $this->task->exitCode    = 0;
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

it('marks a successful task as succeeded', function (): void
{
    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 1.5));

    expect(LaravelScheduledCommandHistory::first())
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::SUCCEEDED)
        ->completed_at
        ->not->toBeNull()
        ->duration_ms
        ->toBe(1500);
});

it('captures output on success', function (): void
{
    file_put_contents($this->task->output, 'Command output here');

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.5));

    expect(LaravelScheduledCommandHistory::first())
        ->output
        ->toBe('Command output here')
        ->error
        ->toBeNull();
});

it('marks a non-zero exit code as failed', function (): void
{
    $this->task->exitCode = 1;

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 2.0));

    expect(LaravelScheduledCommandHistory::first())
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::FAILED);
});

it('stores output as error when task fails', function (): void
{
    $this->task->exitCode = 1;

    file_put_contents($this->task->output, 'Something went wrong');

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 1.0));

    expect(LaravelScheduledCommandHistory::first())
        ->error
        ->toBe('Something went wrong')
        ->output
        ->toBeNull();
});

it('pulls the cache key on success so it is removed', function (): void
{
    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.1));

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'framework/schedule-test-mutex'))
        ->toBeNull();
});

it('preserves the cache key on failure for the failed listener', function (): void
{
    $this->task->exitCode = 1;

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.1));

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'framework/schedule-test-mutex'))
        ->not->toBeNull();
});

it('handles missing output file gracefully', function (): void
{
    unlink($this->task->output);

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.5));

    expect(LaravelScheduledCommandHistory::first())
        ->output
        ->toBeNull()
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::SUCCEEDED);
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

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.5));

    expect(true)
        ->toBeTrue();
});

it('ignores non-string cache values', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(segments: 'framework/schedule-test-mutex');
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
        value: 12345,
        expires: now()->addHour(),
        segments: 'framework/schedule-test-mutex',
    );

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.5));

    expect(LaravelScheduledCommandHistory::first())
        ->status
        ->toBe(LaravelScheduledRunStatusEnum::RUNNING);
});

it('treats empty output as null', function (): void
{
    file_put_contents($this->task->output, '   ');

    Event::dispatch(new ScheduledTaskFinished($this->task, runtime: 0.5));

    expect(LaravelScheduledCommandHistory::first())
        ->output
        ->toBeNull();
});
