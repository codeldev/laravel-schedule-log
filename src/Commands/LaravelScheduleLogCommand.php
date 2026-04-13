<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Commands;

use CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(
    'schedule-log:prune',
    'Prune scheduled command runs older than the configured retention period'
)]
/** @internal */
final class LaravelScheduleLogCommand extends Command
{
    public function __construct(private readonly LaravelScheduledCommandHistory $history)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try
        {
            /** @var int $days */
            $days = config('schedule-log.prune_days', 365);

            /** @var int $deleted */
            $deleted = $this->history::query()
                ->whereDate('created_at', '<', now()->subDays($days))
                ->delete();

            $this->info('Pruned ' . $deleted . ' schedule history entries older than ' . $days . ' days.');

            return self::SUCCESS;
        }
        catch (Throwable $throwable)
        {
            report($throwable);

            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}
