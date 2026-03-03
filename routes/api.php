<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

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
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // =======================================
    // ROLES
    // =======================================
    Route::get('/roles', [RoleController::class, 'index']);
    
    // =======================================
    // EQUIPOS (solo admin)
    // =======================================
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/{id}/members', [TeamController::class, 'addMembers']);
    Route::delete('/teams/{id}/members/{memberId}', [TeamController::class, 'removeMember']);
    
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
    // NOTIFICACIONES
    // =======================================
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});