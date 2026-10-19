<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\v2\AppointmentController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppointmentHeaderIndexController extends Controller
{
    public function __invoke(Request $request, AppointmentController $controller): mixed
    {
        abort_unless(
            $request->header('Accept') === 'application/vnd.clinicflow.v2+json',
            Response::HTTP_NOT_ACCEPTABLE,
            'Versão da API não suportada.',
        );

        return $controller($request);
    }
}
