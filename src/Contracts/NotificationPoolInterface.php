<?php

namespace ZankoKhaledi\Notifications\Contracts;

interface NotificationPoolInterface
{

    public function pool(array $drivers = []):NotificationAsyncInterface;
}