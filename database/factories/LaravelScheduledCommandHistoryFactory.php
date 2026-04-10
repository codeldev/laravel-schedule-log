<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Database\Factories;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaravelScheduledCommandHistory> */
class LaravelScheduledCommandHistoryFactory extends Factory
{
    /** @var class-string<LaravelScheduledCommandHistory> */
    protected $model = LaravelScheduledCommandHistory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startedAt = CarbonImmutable::now()->subMinutes(fake()->numberBetween(1, 60));

        return [
            'scheduled_command_id' => LaravelScheduledCommand::factory(),
            'started_at'           => $startedAt,
            'completed_at'         => $startedAt->addSeconds(fake()->numberBetween(1, 120)),
            'status'               => LaravelScheduledRunStatusEnum::SUCCEEDED,
            'output'               => fake()->optional()->sentence(),
            'error'                => null,
            'duration_ms'          => fake()->numberBetween(100, 120000),
        ];
    }

    public function running(): static
    {
        return $this->state(fn (): array => [
            'status'       => LaravelScheduledRunStatusEnum::RUNNING,
            'completed_at' => null,
            'duration_ms'  => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => LaravelScheduledRunStatusEnum::FAILED,
            'error'  => fake()->sentence(),
        ]);
    }
}
