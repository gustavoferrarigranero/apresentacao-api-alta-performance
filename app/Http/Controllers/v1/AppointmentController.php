<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AppointmentResource;
use App\Jobs\SendAppointmentWebhook;
use App\Models\Appointment;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with('patient')
            ->get();

        return AppointmentResource::collection($appointments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where(
                    'clinic_id', $request->integer('clinic_id')
                ),
            ],
            'starts_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = DB::transaction(function () use ($validated) {
            $appointment = Appointment::create([...$validated, 'status' => 'scheduled']);

            $event = [
                'event_id' => (string) Str::uuid(),
                'event' => 'appointment.created',
                'occurred_at' => now()->utc()->toIso8601String(),
                'data' => [
                    'appointment_id' => $appointment->id,
                    'status' => $appointment->status,
                ],
            ];

            DB::afterCommit(function () use ($appointment, $event) {
                WebhookEndpoint::query()
                    ->where('clinic_id', $appointment->clinic_id)
                    ->where('active', true)
                    ->each(
                        fn (WebhookEndpoint $endpoint) =>
                            SendAppointmentWebhook::dispatch($endpoint->id, $event)
                    );
            });

            return $appointment;
        });

        return new AppointmentResource(
            $appointment->load('patient:id,name')
        );
    }
}
