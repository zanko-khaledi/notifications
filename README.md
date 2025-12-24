# Notifications Package

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
```

## 🚀 Usage
### 1. Sending a notification

```php

use ZankoKhaledi\Notifications\NotificationService;
use App\Notifications\MyNotification; // implements NotificationInterface

$service = app(NotificationService::class);

// Send synchronously
$service->send(new MyNotification());

// Send asynchronously (queued)
$service->send(new MyNotification(), true);

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

#### PHP >= 8.1

#### Laravel >= 11

#### Queue driver configured (for async jobs)

### 📜 License


---

✅ This README now includes **System driver** alongside **Telegram driver**, installation, features, usage examples, abstract `Notification` class, and package structure.

Would you like me to also add a **Quick Demo Project section** (migration + event + driver + job) so users can test broadcasting and async notifications end-to-end?