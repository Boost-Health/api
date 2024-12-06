<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Musonza\Chat\Traits\Messageable;

abstract class AbstractUser extends Model
{
    use Messageable;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    abstract public static function fromRequest(array $payload): AbstractUser;
}
