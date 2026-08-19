<?php

namespace ZankoKhaledi\Notifications;

use ZankoKhaledi\Notifications\Contracts\NotificationAsyncInterface;
use ZankoKhaledi\Notifications\contracts\NotificationInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationPoolInterface;
use ZankoKhaledi\Notifications\Contracts\NotificationServiceInterface;
use ZankoKhaledi\Notifications\Jobs\NotificationJob;

class NotificationService implements NotificationAsyncInterface, NotificationPoolInterface, NotificationServiceInterface
{
    private array $drivers = [];

    private(set) ?string $queue = null;
    private(set) ?string $connection = null;

    /**
     * @param NotificationInterface $notification
     * @param bool $async
     * @return void
     */
    public function send(NotificationInterface $notification, bool $async = false): void
    {
        match ($async) {
            true => $this->handleAsync($notification),
            default => $notification->send()
        };
    }

    /**
     * @param string $queue
     * @return $this
     */
    public function onQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @param string $connection
     * @return $this
     */
    public function onConnection(string $connection): static
    {
        $this->connection = $connection;
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
            $notification = is_string($notification)
                ? app($notification)
                : $notification;

            $result = $closure($notification);

            if ($result instanceof NotificationInterface) {
                $this->send($result, async: true);
            }
        }
    }


    /**
     * 
     *@param NotificationInterface $notification
     *@return void
     */
    private function handleAsync(NotificationInterface $notification): void
    {
        $queue = !is_null($this->queue) ?
            $this->queue : "notification";
        $connection = !is_null($this->connection) ?
            $this->queue : config("queue.default", "redis");

        dispatch(new NotificationJob($notification))->onConnection($connection)->onQueue($queue);
    }
}
