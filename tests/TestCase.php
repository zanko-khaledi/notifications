<?php

namespace ZankoKhaledi\Notifications\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ZankoKhaledi\Notifications\NotificationServiceProvider;

abstract class TestCase extends Orchestra
{

    protected function getPackageProviders($app): array
    {
        return [
            NotificationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
