<?php

declare(strict_types=1);

return [
    /**
     * ------------------------------------------------------------------------------
     *  Prune configuration
     * ------------------------------------------------------------------------------
     * Number of days to retain schedule history entries. The built-in prune command
     * (php artisan schedule-log:prune) deletes entries older than this.
     */
    'prune_days' => (int) env('SCHEDULE_LOG_PRUNE_DAYS', 365),

    /**
     * ------------------------------------------------------------------------------
     *  Model configuration
     * ------------------------------------------------------------------------------
     * Eloquent models used by the package. Override these with your own subclasses
     * to add custom behaviour, scopes, or relationships.
     */
    'models' => [
        // Stores the scheduled commands
        'commands' => CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommand::class,
        // Stores the history of each command run
        'history'  => CodelDev\LaravelScheduleLog\Models\LaravelScheduledCommandHistory::class,
    ],

    /**
     * ------------------------------------------------------------------------------
     *  Table configuration
     * ------------------------------------------------------------------------------
     *  Database table names used by the package. Change these if the defaults
     *  conflict with existing tables in your application.
     */
    'tables' => [
        // Stores the scheduled commands
        'commands' => env('SCHEDULE_LOG_TABLE_COMMANDS', 'scheduled_commands'),
        // Stores the history of each command run
        'history'  => env('SCHEDULE_LOG_TABLE_HISTORY', 'scheduled_commands_history'),
    ],
];
