<?php

namespace ZankoKhaledi\Notifications\Contracts;

interface NotificationServiceInterface
{

    /**
     * @param NotificationInterface $notification
     * @param bool $async
     * @return void
     */
    public function send(NotificationInterface $notification,bool $async = false): void;
}
