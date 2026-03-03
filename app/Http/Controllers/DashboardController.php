<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Team;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $totalCitas = Appointment::count();
        $citasPendientes = Appointment::whereIn('status', ['solicitada', 'pendiente_cotizacion'])->count();
        $citasHoy = Appointment::whereDate('scheduled_date', Carbon::today())->count();
        $cotizacionesPendientes = Appointment::where('status', 'cotizada')->count();

        $citasPorEstado = Appointment::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $empleados = User::whereIn('role_id', [2,3])
            ->withCount(['clientAppointments as citas_completadas' => function($q) {
                $q->where('status', 'ejecutada');
            }])
            ->get();

        $equipos = Team::withCount(['appointments as citas_completadas' => function($q) {
                $q->where('status', 'ejecutada');
            }])
            ->get();

        $citasPorDia = Appointment::selectRaw('DATE(scheduled_date) as date, count(*) as total')
            ->whereBetween('scheduled_date', [Carbon::now()->subDays(7), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'globales' => [
                'total_citas' => $totalCitas,
                'citas_pendientes' => $citasPendientes,
                'citas_hoy' => $citasHoy,
                'cotizaciones_pendientes' => $cotizacionesPendientes
            ],
            'por_estado' => $citasPorEstado,
            'por_empleado' => $empleados,
            'por_equipo' => $equipos,
            'citas_ultimos_7_dias' => $citasPorDia
        ]);
    }

    public function byEmployee($id)
    {
        $empleado = User::with(['clientAppointments' => function($q) {
            $q->where('status', 'ejecutada');
        }])->findOrFail($id);

        return response()->json($empleado);
    }

    public function byTeam($id)
    {
        $equipo = Team::with(['appointments' => function($q) {
            $q->where('status', 'ejecutada');
        }, 'members'])->findOrFail($id);

        return response()->json($equipo);
    }
}