<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use ZankoKhaledi\Notifications\Contracts\NotificationInterface;
use ZankoKhaledi\Notifications\Jobs\NotificationJob;
use ZankoKhaledi\Notifications\Notification;
use ZankoKhaledi\Notifications\NotificationService;


it('has a Laravel application', function () {
    expect(app())
        ->toBeInstanceOf(Illuminate\Foundation\Application::class);
});

it('sends notification synchronously', function () {

    $fakeNotification = Mockery::mock(NotificationInterface::class);

    $fakeNotification
        ->shouldReceive('send')
        ->once();

    $service = app(NotificationService::class);

    $service->send($fakeNotification);
});


it('dispatches notification asynchronously', function () {

    Queue::fake();

    $fakeNotification = Mockery::mock(NotificationInterface::class);

    $service = app(NotificationService::class);

    $service->onQueue('notifications')->onConnection('redis')->send($fakeNotification, async: true);

    Queue::assertPushed(NotificationJob::class, 1);
});


it('sends notification when the job is executed', function () {

    $fakeNotification = Mockery::mock(NotificationInterface::class);

    $fakeNotification
        ->shouldReceive('setUser')
        ->shouldReceive('setMessage')
        ->shouldReceive('send')
        ->once();

    $job = new NotificationJob($fakeNotification);

    $job->handle();
});


it('sends notifications via pool method', function () {

    $notificationA = new class extends Notification implements NotificationInterface {};

    $notificationB = new class extends Notification implements NotificationInterface {};

    Queue::fake();

    $service = app(NotificationService::class);

    $service->pool([
        $notificationA::class,
        $notificationB::class
    ])->then(function (NotificationInterface $notification) {
        return $notification->setTitle("Title")->setMessage("Hello World");
    });

    Queue::assertPushed(NotificationJob::class, 2);
});
