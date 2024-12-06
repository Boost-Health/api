<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Message;

class ProcessMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Message $message) {}

    public function handle(): void
    {
        Log::info('job:process-message', $this->message->toArray());
    }
}
