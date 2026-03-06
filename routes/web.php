<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

// Ruta temporalmente publica para maquetar frontend.
// Luego se protege con middleware cuando definamos el flujo final de auth web.
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('dashboard');
