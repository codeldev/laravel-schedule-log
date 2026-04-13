<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Listeners;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Throwable;

/** @internal */
final readonly class RecordScheduledTaskFailed
{
    public function __construct(
        private LaravelScheduledCommandHistory $commandHistory
    ) {}

    public function handle(ScheduledTaskFailed $event): void
    {
        try
        {
            if ($runId = $this->getRunId($event))
            {
                $this->updateFailedTask(
                    event   : $event,
                    runModel: $this->commandHistory->findOrFail($runId),
                );
            }
        }
        catch (Throwable $throwable)
        {
            report($throwable);
        }
    }

    private function getRunId(ScheduledTaskFailed $event): ?string
    {
        $runId = LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(
            segments: $event->task->mutexName(),
        );

        return is_string($runId) ? $runId : null;
    }

    private function updateFailedTask(ScheduledTaskFailed $event, LaravelScheduledCommandHistory $runModel): void
    {
        $now = CarbonImmutable::now();

        $runModel->update([
            'completed_at' => $runModel->completed_at ?? $now,
            'duration_ms'  => $runModel->duration_ms  ?? (int) round($now->diffInMilliseconds($runModel->started_at, absolute: true)),
            'error'        => $event->exception->getMessage(),
            'status'       => LaravelScheduledRunStatusEnum::FAILED,
        ]);
    }
}
