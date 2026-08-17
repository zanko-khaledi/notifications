


# Notifications Package

[

![Latest Version](https://img.shields.io/packagist/v/zanko-khaledi/notifications.svg)

](https://packagist.org/packages/zanko-khaledi/notifications)
[

![Total Downloads](https://img.shields.io/packagist/dt/zanko-khaledi/notifications.svg)

](https://packagist.org/packages/zanko-khaledi/notifications)
[

![License](https://img.shields.io/github/license/zanko-khaledi/notifications.svg)

](https://github.com/zanko-khaledi/notifications/blob/master/LICENSE)
[

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777bb4.svg)

](https://php.net)
[

![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D11-ff2d20.svg)

](https://laravel.com)

A flexible, event-driven **Laravel package** for managing and dispatching notifications.  
It provides both **synchronous** and **asynchronous (queued)** notification handling, with support for notification pools, custom drivers, and a base `Notification` class for building reusable notification types.

---

## ✨ Features

- **Synchronous & Asynchronous sending**  
  Send notifications immediately or queue them for background processing.

- **Job-based async execution**  
  Uses Laravel’s `NotificationJob` to handle queued notifications.

- **Notification pools**  
  Group multiple notification drivers together and process them in sequence.

- **Contracts for extensibility**  
  Define your own notification drivers by implementing the provided interfaces.

- **Abstract Notification base class**  
  Provides a reusable foundation for building custom notification types with user, notifiable models, and collections.

---

## 📦 Installation

Require the package in your Laravel app:

```bash
composer require zanko-khaledi/notifications:@dev
```

## ⚙️ Configuration

``` bash
 php artisan vendor:publish --tag=notifications-config
 
 php artisan vendor:publish --tag=notifications-model
 
 php artisan migrate
```

## 🚀 Usage
### 1. Sending a notification

```php

use ZankoKhaledi\Notifications\NotificationService;
use App\Notifications\MyNotification; // implements NotificationInterface

$service = app(NotificationService::class);

$user = auth()->user();
$notifiables = User::query()->where("id", ">", 2)->get();

$notification = app(MyNotification::class);
$notification->setUser($user)->setTitle("New notification")
        ->setMessage("Hello World")->setNotifiable($notifiables)

// Send synchronously
$service->send($notification);

// Send asynchronously (queued)
$service->send($notification, true);

```

### 2. Using a pool of drivers
```php
use App\Models\User;
use ZankoKhaledi\Notifications\Contracts\NotificationInterface;
use ZankoKhaledi\Notifications\NotificationService;
use App\Notifications\Telegram;
use App\Notifications\System;

$user = auth()->user();
$notifiables = User::query()->where("id", ">", 2)->get();

$notificationService = app(NotificationService::class);

// notifications sent via queue & background processing with pool 

$notificationService->pool([
    Telegram::class,
    System::class
])->then(fn(NotificationInterface $notification) =>
    $notification->setUser($user)
                 ->setTitle("Hi")
                 ->setMessage("Hello World")
                 ->setNotifiable($notifiables)
);
```
This example demonstrates how to send notifications to multiple drivers (Telegram, System) using a pool.

### 3. Extending the abstract Notification class & implementing NotificationInterface

```php
use ZankoKhaledi\Notifications\Notification;
use ZankoKhaledi\Notifications\Contracts\NotificationInterface;

class SystemNotification extends Notification implements NotificationInterface
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function setTitle(string $text = "") : static
    {
       $this->title = $text;
       return $this;
    }
    
    public function setMessage(string $text = "") : static
    {
        $this->message = $text;
        return $this;
    }
    
    public function send() : \ZankoKhaledi\Notifications\Models\Notification
    {
       $model = parent::send();
       
       // you can send you're notification via broadcast channel to websocket server then any consumers could catch the notification
       broadcast(new NotificationEvent($model));
       return $model;
    }
}

```

### 4. Example Driver: Telegram

```php

namespace App\Services\Notifications;

use Exception;
use Illuminate\Support\Facades\Http;
use ZankoKhaledi\Notifications\Contracts\NotificationInterface;
use ZankoKhaledi\Notifications\Models\Notification as ModelsNotification;
use ZankoKhaledi\Notifications\Notification;

class Telegram extends Notification implements NotificationInterface
{
    public function setTitle(string $text = ''): static
    {
        $this->title = $text;
        return $this;
    }

    public function setMessage(string $text = ''): static
    {
        $this->message = $text;
        return $this;
    }

    public function send(): ModelsNotification
    {
        $model = parent::send();

        $url = config('notifications.telegram_url', 'https://api.telegram.org/bot'.env('TELEGRAM_BOT_TOKEN').'/sendMessage');

        $response = Http::post($url, [
            'chat_id' => $this->getUser()?->id,
            'text'    => $this->getMessage(),
        ]);

        if ($response->successful()) {
            return $model;
        }

        throw new Exception(sprintf('Telegram API error: %s', $response->body()));
    }
}

```

## 🤔 Why not just use Laravel's built-in notifications?

Laravel's notification system is great for single-channel, fire-and-forget messages. This package adds what's missing for larger systems:

- **Driver pools** — dispatch the same notification across multiple channels (Telegram, System, etc.) in one call, instead of writing separate `Notification` classes per channel.
- **First-class async by default** — `send($notification, true)` queues automatically via a dedicated job, no manual `ShouldQueue` boilerplate.
- **Reusable base class** — build your own notification types once, extend them everywhere, instead of duplicating structure across Laravel's per-notification classes.

Use Laravel's native notifications for simple cases. Use this package when you need pooled, multi-driver, queue-first dispatching.

## 📂 Package Structure

```
src/
 ├── NotificationService.php
 ├── Notification.php (abstract base class)
 ├── Jobs/
 │    └── NotificationJob.php
 └── Contracts/
      ├── NotificationInterface.php
      ├── NotificationAsyncInterface.php
      ├── NotificationPoolInterface.php
      └── NotificationServiceInterface.php
```

## ⚡ Requirements

#### PHP >= 8.2

#### Laravel >= 11

#### Queue driver configured (for async jobs)

### 📜 License
This package is open-sourced software licensed under the MIT license