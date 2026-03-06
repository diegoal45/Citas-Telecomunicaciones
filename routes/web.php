<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardRedirectController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Rutas de autenticación
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

// Rutas de dashboard protegidas
// Rutas de dashboard (manejo de autenticación en el frontend)
Route::get('/dashboard', [DashboardRedirectController::class, 'index'])->name('dashboard');
Route::get('/dashboard/client', [DashboardRedirectController::class, 'client'])->name('dashboard.client');
Route::get('/admin/dashboard', [DashboardRedirectController::class, 'admin'])->name('dashboard.admin');
Route::get('/tecnico/dashboard', [DashboardRedirectController::class, 'techLeader'])->name('dashboard.tecnico');
Route::get('/technician/dashboard', [DashboardRedirectController::class, 'technician'])->name('dashboard.technician');

Route::get('/appointments/create', function () {
    return Inertia::render('Appointments/Create');
})->name('appointments.create');

// Rutas de perfil y configuración
Route::get('/profile', function () {
    return Inertia::render('Profile/Edit');
})->name('profile.edit');

Route::get('/settings', function () {
    return Inertia::render('Settings/Index');
})->name('settings.index');
