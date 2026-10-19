<?php

use App\Http\Controllers\v1\AppointmentController as AppointmentV1Controller;
use App\Http\Controllers\v2\AppointmentHeaderIndexController;
use App\Http\Controllers\v3\AppointmentController as AppointmentV3Controller;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

Route::group(['prefix' => 'v1'], function () {
    Route::get('/appointments', [AppointmentV1Controller::class, 'index']);
    Route::post('/appointments', [AppointmentV1Controller::class, 'store']);
});

Route::post('/webhooks/partner', [AppointmentV1Controller::class, 'receiveWebhook']);

Route::group(['prefix' => 'v2'], function () {
    Route::get('/appointments', AppointmentHeaderIndexController::class);
});

