<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function index()
    {
        // Redirigir según el rol del usuario autenticado
        if (Auth::check()) {
            $roleName = Auth::user()->role->name ?? null;
            
            if ($roleName === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($roleName === 'tecnico_lider') {
                return redirect('/tecnico/dashboard');
            } elseif ($roleName === 'tecnico') {
                return redirect('/technician/dashboard');
            } else {
                return redirect('/dashboard/client');
            }
        }
        
        return redirect('/login');
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
