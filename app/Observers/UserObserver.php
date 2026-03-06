<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event - solo para usuarios nuevos (clientes registrándose)
     */
    public function created(User $user)
    {
        // Solo crear notificación de bienvenida si es un usuario nuevo (rol cliente)
        // y no es un usuario creado por admin
        if ($user->id_rol == 4) { // Cliente/User
            Notification::create([
                'user_id' => $user->id,
                'type' => 'welcome',
                'title' => '¡Bienvenido!',
                'message' => "Hola {$user->name}, ¡bienvenido a ExCitel! Tu cuenta ha sido creada exitosamente.",
                'data' => json_encode(['user_id' => $user->id, 'user_name' => $user->name]),
                'is_read' => false
            ]);
        }
    }
}
