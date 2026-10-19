<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Api\AppointmentIndexController;
use App\Http\Controllers\Controller;
use App\Http\Resources\v2\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{

    public function __invoke(Request $request)
    {
        $appointments = Appointment::query()
            ->with(['patient', 'clinic'])
            ->get();

        return AppointmentResource::collection($appointments);
    }
}
