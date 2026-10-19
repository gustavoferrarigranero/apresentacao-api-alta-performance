<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_event_id',
        'webhook_endpoint_id',
        'status_code',
        'response_body',
        'attempted_at'
    ];
}
