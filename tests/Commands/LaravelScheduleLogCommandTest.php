<?php

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;

it('prunes history entries older than configured days', function (): void
{
    config()
        ->set('laravel-schedule-log.prune_days', 30);

    $old = LaravelScheduledCommandHistory::factory()->create([
        'created_at' => now()->subDays(31),
    ]);

    $recent = LaravelScheduledCommandHistory::factory()->create([
        'created_at' => now()->subDays(5),
    ]);

    $this->artisan('schedule-log:prune')
        ->assertSuccessful();

    expect(LaravelScheduledCommandHistory::find($old->id))
        ->toBeNull()
        ->and(LaravelScheduledCommandHistory::find($recent->id))
        ->not->toBeNull();
});

it('outputs the number of pruned entries', function (): void
{
    config()
        ->set('laravel-schedule-log.prune_days', 10);

    LaravelScheduledCommandHistory::factory()
        ->count(3)
        ->create(['created_at' => now()->subDays(15)]);

    $this->artisan('schedule-log:prune')
        ->expectsOutputToContain('Pruned 3 schedule history entries older than 10 days')
        ->assertSuccessful();
});

it('returns success when there is nothing to prune', function (): void
{
    config()
        ->set('laravel-schedule-log.prune_days', 30);

    $this->artisan('schedule-log:prune')
        ->expectsOutputToContain('Pruned 0')
        ->assertSuccessful();
});

it('returns failure and reports the exception when an error occurs', function (): void
{
    config()
        ->set('laravel-schedule-log.prune_days', 'not-a-number');

    $this->artisan('schedule-log:prune')
        ->assertFailed();
});
