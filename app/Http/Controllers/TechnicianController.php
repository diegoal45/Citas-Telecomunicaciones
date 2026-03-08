<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Obtener equipos a los que pertenece el técnico
        $teams = $user->teams()->with('leader')->get();
        $teamIds = $teams->pluck('id')->toArray();

        // Obtener citas de sus equipos solo cuando ya son relevantes para ejecucion.
        $appointments = Appointment::whereIn('team_id', $teamIds)
            ->whereIn('status', ['para_ejecucion', 'programada', 'ejecutada', 'cancelada'])
            ->with('client', 'team', 'quotation')
            ->orderBy('scheduled_date', 'asc')
            ->get();

        return response()->json([
            'teams' => $teams,
            'appointments' => $appointments,
        ]);
    }
}
