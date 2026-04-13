<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use Illuminate\Support\Facades\Cache;

it('puts and gets a value from cache', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
        value: 'test-id',
        expires: now()->addHour(),
        segments: 'mutex-name',
    );

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'mutex-name'))
        ->toBe('test-id');
});

it('pulls a value removing it from cache', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
        value: 'test-id',
        expires: now()->addHour(),
        segments: 'mutex-name',
    );

    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(segments: 'mutex-name'))
        ->toBe('test-id')
        ->and(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'mutex-name'))
        ->toBeNull();
});

it('returns null when key does not exist', function (): void
{
    expect(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: 'nonexistent'))
        ->toBeNull()
        ->and(LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(segments: 'nonexistent'))
        ->toBeNull();
});

it('builds namespaced cache keys with segments', function (): void
{
    LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
        value: 'abc',
        expires: now()->addHour(),
        segments: 'segment-one',
    );

    expect(Cache::get('scheduled:run:segment-one'))->toBe('abc');
});
