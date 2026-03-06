<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardRedirectController extends Controller
{



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
}
