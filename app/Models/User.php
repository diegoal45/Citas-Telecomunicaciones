<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ← ESTA LÍNEA ES CRUCIAL
use App\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ← HasApiTokens debe estar aquí

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'id_rol'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relaciones
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function ledTeams()
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members');
    }

    public function clientAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function cancelledAppointments()
    {
        return $this->hasMany(Appointment::class, 'cancelled_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}