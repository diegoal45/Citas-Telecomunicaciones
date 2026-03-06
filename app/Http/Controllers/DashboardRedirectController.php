<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function index()
    {
        // No redirecionar en el servidor - dejar que el frontend maneje esto
        // El frontend tiene el token y sabe a qué dashboard ir según el rol
        return Inertia::render('Dashboard/Index');
    }

    public function client()
    {
        return Inertia::render('Dashboard/Index');
    }

    public function admin()
    {
        return Inertia::render('Dashboard/Admin');
    }

    public function techLeader()
    {
        return Inertia::render('Dashboard/TechLeader');
    }

    public function technician()
    {
        return Inertia::render('Dashboard/Technician');
    }
}
