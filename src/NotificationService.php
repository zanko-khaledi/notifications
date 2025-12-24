<?php

namespace ZankoKhaledi\Notifications;

use ZankoKhaledi\Notifications\Contracts\NotificationAsyncInterface;
use ZankoKhaledi\Notifications\contracts\NotificationInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationPoolInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationServiceInterface;
use ZankoKhaledi\Notifications\Jobs\NotificationJob;

class NotificationService implements NotificationAsyncInterface,NotificationPoolInterface,NotificationServiceInterface
{

    private array $drivers = [];

    /**
     * @param NotificationInterface $notification
     * @param bool $async
     * @return void
     */
    public function send(NotificationInterface $notification, bool $async = false): void
    {
        match ($async) {
            true => dispatch(new NotificationJob($notification)),
            default => $notification->send()
        };
    }

    /**
     * @param array $drivers
     * @return NotificationAsyncInterface
     */
    public function pool(array $drivers = []): NotificationAsyncInterface
    {
        $this->drivers = $drivers;
        return $this;
    }

    /**
     * @param \Closure $closure
     * @return void
     */
    public function then(\Closure $closure): void
    {
        foreach ($this->drivers as $notification) {
            $result = $closure(app($notification));

            if ($result instanceof NotificationInterface) {
                $this->send($result, true);
            }
        }
    }
}