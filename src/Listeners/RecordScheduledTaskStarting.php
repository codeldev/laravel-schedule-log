<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Listeners;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\DB;
use Throwable;

/** @internal */
final readonly class RecordScheduledTaskStarting
{
    public function __construct(
        private LaravelScheduledCommand $scheduledCommand
    ) {}

    public function handle(ScheduledTaskStarting $event): void
    {
        try
        {
            $this->recordTask(
                event  : $event,
                command: $this->extractCommandSignature($event->task->command)
            );
        }
        catch (Throwable $throwable)
        {
            report($throwable);
        }
    }

    /** @throws Throwable */
    private function recordTask(ScheduledTaskStarting $event, ?string $command): void
    {
        if ($command !== null)
        {
            DB::transaction(function () use ($command, $event): void
            {
                $scheduledCommand = $this->getScheduledCommand($command, $event);
                $historicRunEntry = $this->storeRunEntry($scheduledCommand);

                LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->put(
                    value   : $historicRunEntry->id,
                    expires : now()->addHours(24),
                    segments: $event->task->mutexName(),
                );
            });
        }
    }

    private function getScheduledCommand(string $command, ScheduledTaskStarting $event): LaravelScheduledCommand
    {
        return $this->scheduledCommand->updateOrCreate(
            ['command' => $command],
            ['description' => $event->task->description ?? null],
        );
    }

    private function storeRunEntry(LaravelScheduledCommand $command): LaravelScheduledCommandHistory
    {
        return $command->history()->create([
            'started_at' => CarbonImmutable::now(),
            'status'     => LaravelScheduledRunStatusEnum::RUNNING,
        ]);
    }

    private function extractCommandSignature(?string $command): ?string
    {
        return ($command !== null && preg_match("/['\"]?artisan['\"]?\s+(\S+)/", $command, $matches))
            ? $matches[1]
            : null;
    }
}
