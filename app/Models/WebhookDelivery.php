<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_endpoint_id',
        'event_id',
        'event_name',
        'attempt',
        'response_status',
        'response_body',
        'duration_ms',
        'successful',
    ];

    protected function casts(): array
    {
        return ['successful' => 'boolean'];
    }
}
