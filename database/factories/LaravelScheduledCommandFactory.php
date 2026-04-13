<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Database\Factories;

use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaravelScheduledCommand> */
class LaravelScheduledCommandFactory extends Factory
{
    /** @var class-string<LaravelScheduledCommand> */
    protected $model = LaravelScheduledCommand::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'command'     => fake()->unique()->word() . ':' . fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
