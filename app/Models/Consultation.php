<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use App\Enums\OrderType;
use App\Objects\MessageObject;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
class Consultation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => ConsultationStatus::class,
        'order_type' => OrderType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    #[Scope]
    public function forMessage(Builder $query, MessageObject $messageObject): Builder
    {
        $query
            ->whereUserId($messageObject->to->user->id)
            ->whereDoctorId($messageObject->from->user->id)
            ->whereStatus(ConsultationStatus::PENDING);
    }
}
