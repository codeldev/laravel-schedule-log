<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog;

use CodelDev\LaravelScheduleLog\Commands\LaravelScheduleLogCommand;
use CodelDev\LaravelScheduleLog\Listeners\RecordScheduledTaskFailed;
use CodelDev\LaravelScheduleLog\Listeners\RecordScheduledTaskFinished;
use CodelDev\LaravelScheduleLog\Listeners\RecordScheduledTaskStarting;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/** @internal */
final class LaravelScheduleLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-schedule-log')
            ->hasConfigFile()
            ->hasMigration('create_schedule_log_table')
            ->hasCommand(LaravelScheduleLogCommand::class);
    }

    public function bootingPackage(): void
    {
        $this->bindModels();
        $this->registerListeners();
    }

    private function registerListeners(): void
    {
        Event::listen(ScheduledTaskStarting::class, RecordScheduledTaskStarting::class);
        Event::listen(ScheduledTaskFinished::class, RecordScheduledTaskFinished::class);
        Event::listen(ScheduledTaskFailed::class, RecordScheduledTaskFailed::class);
    }

    private function bindModels(): void
    {
        /** @var class-string $commandModel */
        $commandModel = config('schedule-log.models.commands', LaravelScheduledCommand::class);

        /** @var class-string $historyModel */
        $historyModel = config('schedule-log.models.history', LaravelScheduledCommandHistory::class);

        $this->app->bind(LaravelScheduledCommand::class, $commandModel);
        $this->app->bind(LaravelScheduledCommandHistory::class, $historyModel);
    }
}
