<?php

namespace ZankoKhaledi\Notifications;

use App\Models\User;
use ZankoKhaledi\Notifications\Models\Notification as NotificationModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

abstract class Notification
{
    protected string $title;

    protected string $message;

    protected NotificationModel $notification;

    protected ?User $user = null;

    protected ?Model $notifiable = null;

    protected ?Collection $notifiables = null;

    protected ?string $type = null;

    protected string $driver;

    protected array $details;


    public function __construct()
    {
        $this->notification = new NotificationModel();
        $this->title = "Notification";
        $this->notifiable = null;
        $this->notifiables = null;
        $this->user = Auth::user() ?? null;
        $this->details = [];
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return Model|null
     */
    public function getNotifiable(): Model|null
    {
        return $this->notifiable;
    }

    /**
     * @return Collection|null
     */
    public function getNotifiables(): ?Collection
    {
        return $this->notifiables;
    }


    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(?User $user = null): static
    {
        $this->user ??= $user;
        return $this;
    }

    /**
     * @param Model|Collection|null $notifiable
     * @return $this
     */
    public function setNotifiable(Model|Collection|null $notifiable): static
    {
        if ($notifiable instanceof Model) {
            $this->notifiable = $notifiable;
        } else {
            $this->notifiables = $notifiable instanceof EloquentCollection ? $notifiable->toBase() : $notifiable;
        }

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setTitle(string $text = ''): static
    {
        $this->title = $text;
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setMessage(string $text = ''): static
    {
        $this->message = $text;
        return $this;
    }


    /**
     * @param array $data
     * @return $this
     */
    public function setDetails(array $data = []): static
    {
       $this->details = $data;
       return $this;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @return NotificationModel|\Exception|null
     * @throws \Exception
     */
    public function send(): NotificationModel|\Exception|null
    {
        if (is_null($this->user)) {
            throw new \Exception("You have to set user before calling send method!");
        }
        if (is_null($this->notifiable) && is_null($this->notifiables)) {
            throw new \Exception("You have to set notifiable before calling send method!");
        }

        $notification = $this->notification;

        if (!is_null($this->notifiables) && $this->notifiables instanceof Collection && !$this->notifiables->isEmpty()) {

            $data = [];

            $this->notifiables->chunk(100)->each(function ($items) use (&$data) {
                foreach ($items as $item) {
                    $data[] = [
                        'driver' => static::class,
                        'notifiable_type' => $item::class,
                        'notifiable_id' => $item->id,
                        'user_id' => $this->getUser()?->id,
                        'title' => $this->getTitle() ?? null,
                        'message' => $this->getMessage() ?? null,
                        'details' => json_encode($this->getDetails()),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            });

            $notification->newQuery()->insert($data);
            unset($data);

            return $notification;
        }


        $notification->driver ??= static::class;
        $notification->notifiable_type = $this->notifiable ? get_class($this->notifiable) : null;
        $notification->notifiable_id ??= $this->notifiable?->id;
        $notification->user_id ??= $this->user?->id;
        $notification->title ??= $this->title;
        $notification->message ??= $this->message;
        $notification->details ??= $this->details;
        $notification->created_at = Carbon::now();
        $notification->updated_at = Carbon::now();
        $notification->save();


        return $notification;
    }

    public function __destruct()
    {
        unset($this->notification);
    }
}
