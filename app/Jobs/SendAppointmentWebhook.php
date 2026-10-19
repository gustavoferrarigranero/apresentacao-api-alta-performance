<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAppointmentWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 10;

    public function __construct(public int $endpointId, public array $event) {}

    public function backoff(): array
    {
        return [10, 30, 120, 300];
    }

    public function handle(): void
    {
        $endpoint = WebhookEndpoint::findOrFail($this->endpointId);
        $payload = json_encode($this->event, JSON_THROW_ON_ERROR);
        $startedAt = hrtime(true);

        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->withHeaders([
                    'X-Event-ID' => $this->event['event_id'],
                    'X-Signature' => hash_hmac('sha256', $payload, $endpoint->secret),
                ])
                ->withBody($payload, 'application/json')
                ->post($endpoint->url);

            $this->recordDelivery($endpoint, $response->status(), $startedAt, $response->successful(), $response->body());
        } catch (Throwable $exception) {
            if (! isset($response)) {
                $this->recordDelivery($endpoint, null, $startedAt, false, $exception->getMessage());
            }

            throw $exception;
        }
    }

    private function recordDelivery(WebhookEndpoint $endpoint, ?int $status, int $startedAt, bool $successful, string $response): void
    {
        Log::info($this->event);

        WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_id' => $this->event['event_id'],
            'event_name' => $this->event['event'],
            'attempt' => $this->attempts(),
            'response_status' => $status,
            'response_body' => mb_substr($response, 0, 2000),
            'duration_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
            'successful' => $successful,
        ]);
    }
}