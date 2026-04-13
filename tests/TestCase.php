<?php

declare(strict_types=1);

namespace CodelDev\LaravelScheduleLog\Tests;

use CodelDev\LaravelScheduleLog\LaravelScheduleLogServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected static Migration $migration;

    protected function setUp(): void
    {
        parent::setUp();

        if (! isset(self::$migration))
        {
            $this->prepareMigration();
        }

        self::$migration->up();

        Factory::guessFactoryNamesUsing(
            static fn (string $modelName) => 'CodelDev\\LaravelScheduleLog\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    /** @param Application $app */
    public function getEnvironmentSetUp($app): void
    {
        Model::preventLazyLoading();

        $app['config']->set('database.default', 'testing');
        $app['config']->set('schedule-log', require __DIR__ . '/../config/schedule-log.php');
    }

    /** @param Application $app */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelScheduleLogServiceProvider::class,
        ];
    }

    private function prepareMigration(): void
    {
        self::$migration = include __DIR__ . '/../database/migrations/create_schedule_log_table.php.stub';
    }
}
