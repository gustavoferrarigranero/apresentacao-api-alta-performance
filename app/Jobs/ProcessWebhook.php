<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $webhookEventId) {}

    public function handle(): void
    {
        WebhookEvent::query()
            ->whereKey($this->webhookEventId)
            ->whereNull('processed_at')
            ->update(['processed_at' => now()]);
    }

}
