<?php

namespace ZankoKhaledi\Notifications\Contracts;

interface NotificationAsyncInterface
{
    public function then(\Closure $closure);
}