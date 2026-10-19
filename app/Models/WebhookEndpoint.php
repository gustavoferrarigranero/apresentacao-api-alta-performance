<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'clinic_id',
        'url',
        'secret',
        'active'
    ];
}
