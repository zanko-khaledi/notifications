<?php

namespace ZankoKhaledi\Notifications;

use ZankoKhaledi\Notifications\Contracts\NotificationAsyncInterface;
use ZankoKhaledi\Notifications\contracts\NotificationInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationPoolInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationServiceInterface;
use ZankoKhaledi\Notifications\Jobs\NotificationJob;
use ZankoKhaledi\Notifications\Jobs\UniqueNotificationJob;

class NotificationService implements NotificationAsyncInterface, NotificationPoolInterface, NotificationServiceInterface
{
    private array $drivers = [];

    private(set) bool $unique = false;

    /**
     * @param NotificationInterface $notification
     * @param bool $async
     * @return void
     */
    public function send(NotificationInterface $notification, bool $async = false): void
    {
        if (!$async) {
            $notification->send();
            return;
        }

        if ($this->unique) {
            dispatch(new UniqueNotificationJob($notification));
        } else {
            dispatch(new NotificationJob($notification));
        }

        $this->unique = false;
    }

    /**
     * @return $this
     */
    public function shoudBeUnique():static
    {
        $this->unique = true;
        return $this;
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
                $this->send($result, async: true);
            }
        }
    }
}
