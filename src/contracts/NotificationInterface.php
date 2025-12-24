<?php

namespace ZankoKhaledi\Notifications\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface NotificationInterface
{
    public function setUser(User $user):static;
    public function setNotifiable(Model|Collection|null $notifiable):static;
    public function setTitle(string $text= ""):static;
    public function setMessage(string $text = ""):static;

    public function getTitle():string;
    public function getMessage():string;

    public function getUser():?User;
    public function getNotifiable():Model|Collection|null;
}