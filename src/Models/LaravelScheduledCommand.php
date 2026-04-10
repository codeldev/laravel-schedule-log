<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Models;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Database\Factories\LaravelScheduledCommandFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property-read string $id
 * @property-read string $command
 * @property-read string|null $description
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 * @property-read Collection<int, LaravelScheduledCommandHistory> $history
 */
#[UseFactory(LaravelScheduledCommandFactory::class)]
class LaravelScheduledCommand extends Model
{
    /** @use HasFactory<LaravelScheduledCommandFactory> */
    use HasFactory;

    /** @see HasUuids */
    use HasUuids;

    /** @var list<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        /** @var string */
        return config('laravel-schedule-log.tables.commands', 'scheduled_commands');
    }

    /** @return HasMany<LaravelScheduledCommandHistory, $this> */
    public function history(): HasMany
    {
        /** @var class-string<LaravelScheduledCommandHistory> $model */
        $model = config('laravel-schedule-log.models.history', LaravelScheduledCommandHistory::class);

        return $this->hasMany($model, 'scheduled_command_id');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'command'     => 'string',
            'description' => 'string',
            'created_at'  => 'immutable_datetime',
            'updated_at'  => 'immutable_datetime',
        ];
    }
}
