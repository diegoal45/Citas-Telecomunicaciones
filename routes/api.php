<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TechLeaderController;

// ===========================================
// RUTAS PÚBLICAS
// ===========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===========================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ===========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // =======================================
    // AUTENTICACIÓN Y PERFIL
    // =======================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::get('/profile/photo', [ProfileController::class, 'getPhoto']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    
    // =======================================
    // ROLES
    // =======================================
    Route::get('/roles', [RoleController::class, 'index']);

    // =======================================
    // USUARIOS (CRUD básico)
    // =======================================
    // los endpoints de usuarios permiten listar, obtener por id y
    // manipular registros. puedes protegerlos con middleware o
    // comprobar el rol en el controlador si solo un admin debe usarlos
    Route::apiResource('users', UserController::class);
    
    // =======================================
    // EQUIPOS (solo admin)
    // =======================================
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/{id}/members', [TeamController::class, 'addMembers']);
    Route::delete('/teams/{id}/members/{memberId}', [TeamController::class, 'removeMember']);
    Route::post('/teams/{id}/members/{memberId}/change', [TeamController::class, 'changeMember']);
    
    // =======================================
    // CITAS
    // =======================================
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule']);
    Route::post('/appointments/{id}/assign-team', [AppointmentController::class, 'assignTeam']);
    Route::post('/appointments/{id}/mark-cotizada', [AppointmentController::class, 'markAsCotizada']);
    
    // =======================================
    // COTIZACIONES
    // =======================================
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::put('/quotations/{id}/price', [QuotationController::class, 'setPrice']);
    Route::post('/quotations/{id}/admin-approve', [QuotationController::class, 'adminApprove']);
    Route::post('/quotations/{id}/approve', [QuotationController::class, 'approve']);
    Route::post('/quotations/{id}/reject', [QuotationController::class, 'reject']);
    Route::post('/quotations/{id}/schedule', [QuotationController::class, 'scheduleExecution']);
    
    // =======================================
    // DASHBOARD (solo admin)
    // =======================================
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/employee/{id}', [DashboardController::class, 'byEmployee']);
    Route::get('/dashboard/team/{id}', [DashboardController::class, 'byTeam']);
    
    // =======================================
    // DASHBOARD TECNICO LIDER
    // =======================================
    Route::get('/tecnico/dashboard', [TechLeaderController::class, 'dashboard']);
    Route::put('/appointments/{id}/execute', [TechLeaderController::class, 'executeAppointment']);
    
    // =======================================
    // DASHBOARD TECNICO
    // =======================================
    Route::get('/technician/dashboard', [\App\Http\Controllers\TechnicianController::class, 'dashboard']);
    
    // =======================================
    // NOTIFICACIONES
    // =======================================
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});