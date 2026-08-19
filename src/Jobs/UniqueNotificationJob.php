<?php

namespace ZankoKhaledi\Notifications\Jobs;


use ZankoKhaledi\Notifications\Contracts\NotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UniqueNotificationJob implements ShouldQueue,ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public readonly NotificationInterface $notification
    )
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->notification->send();
    }


    public function uniqueId():string
    {
        return sprintf("notification:%s:%s",$this->notification::class,$this->notification->getUuid());
    }
}
