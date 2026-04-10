<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Listeners;

use Carbon\CarbonImmutable;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledCacheKeyEnum;
use CodelDev\LaravelScheduleLog\Enums\LaravelScheduledRunStatusEnum;
use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Throwable;

/** @internal */
final readonly class RecordScheduledTaskFinished
{
    public function __construct(
        private LaravelScheduledCommandHistory $commandHistory
    ) {}

    public function handle(ScheduledTaskFinished $event): void
    {
        try
        {
            if ($runId = $this->getRunId($event))
            {
                $this->updateFinishedTask(
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

    private function getRunId(ScheduledTaskFinished $event): ?string
    {
        $runId = $event->task->exitCode !== 0
            ? LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->get(segments: $event->task->mutexName())
            : LaravelScheduledCacheKeyEnum::SCHEDULED_RUN->pull(segments: $event->task->mutexName());

        return is_string($runId) ? $runId : null;
    }

    private function updateFinishedTask(ScheduledTaskFinished $event, LaravelScheduledCommandHistory $runModel): void
    {
        $succeeded = $event->task->exitCode === 0;
        $output    = $this->captureOutput($event->task->output);

        $runModel->update([
            'completed_at' => CarbonImmutable::now(),
            'duration_ms'  => (int) round($event->runtime * 1000),
            'output'       => $succeeded ? $output : null,
            'error'        => $succeeded ? null : $output,
            'status'       => $succeeded
                ? LaravelScheduledRunStatusEnum::SUCCEEDED
                : LaravelScheduledRunStatusEnum::FAILED,
        ]);
    }

    private function captureOutput(string $outputPath): ?string
    {
        if (! file_exists($outputPath))
        {
            return null;
        }

        $content = mb_trim((string) file_get_contents($outputPath));

        return $content !== '' ? $content : null;
    }
}
