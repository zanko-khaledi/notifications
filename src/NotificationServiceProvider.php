<?php

namespace ZankoKhaledi\Notifications;

use Illuminate\Support\ServiceProvider;
class NotificationServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/../database/migrations");

        $this->publishes([ __DIR__.'/../config/notifications.php' => config_path('notifications.php') ], 'notifications-config');

        $this->publishes([ __DIR__ . '/../models/Notification.php' => app_path('Models/Notification.php'), ], 'notifications-model');
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/notifications.php', 'notifications' );
    }
}