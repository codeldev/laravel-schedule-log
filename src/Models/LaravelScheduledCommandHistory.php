<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Models;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Database\Factories\LaravelScheduledCommandHistoryFactory;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read string $id
 * @property-read string $scheduled_command_id
 * @property-read CarbonImmutable $started_at
 * @property-read CarbonImmutable|null $completed_at
 * @property-read LaravelScheduledRunStatusEnum $status
 * @property-read string|null $output
 * @property-read string|null $error
 * @property-read int|null $duration_ms
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 * @property-read LaravelScheduledCommand $scheduledCommand
 */
#[UseFactory(LaravelScheduledCommandHistoryFactory::class)]
class LaravelScheduledCommandHistory extends Model
{
    /** @use HasFactory<LaravelScheduledCommandHistoryFactory> */
    use HasFactory;

    /** @see HasUuids */
    use HasUuids;

    /** @var list<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        /** @var string */
        return config('laravel-schedule-log.tables.history', 'scheduled_commands_history');
    }

    /** @return BelongsTo<LaravelScheduledCommand, $this> */
    public function scheduledCommand(): BelongsTo
    {
        /** @var class-string<LaravelScheduledCommand> $model */
        $model = config('laravel-schedule-log.models.commands', LaravelScheduledCommand::class);

        return $this->belongsTo($model);
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'id'                   => 'string',
            'scheduled_command_id' => 'string',
            'started_at'           => 'immutable_datetime',
            'completed_at'         => 'immutable_datetime',
            'status'               => LaravelScheduledRunStatusEnum::class,
            'output'               => 'string',
            'error'                => 'string',
            'duration_ms'          => 'integer',
            'created_at'           => 'immutable_datetime',
            'updated_at'           => 'immutable_datetime',
        ];
    }
}
