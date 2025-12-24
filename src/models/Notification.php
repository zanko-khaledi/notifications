<?php

namespace ZankoKhaledi\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use User;

class Notification extends Model
{
    protected $guarded = [];


    use HasFactory;

    protected $casts = [
        'seen_at' => 'datetime',
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $table ??= config('notifications.table_name');

        if (is_null($table)) {
            $table = 'notifications';
        }

        $this->table = $table;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}