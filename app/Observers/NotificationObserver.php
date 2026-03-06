<?php

namespace App\Observers;

use App\Models\Notification;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     * Envía un correo cuando se crea una notificación
     */
    public function created(Notification $notification): void
    {
        try {
            // Log para debugging
            \Log::info("Enviando notificación por correo", [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'user_email' => $notification->user->email ?? 'no-email',
                'type' => $notification->type,
            ]);
            
            // Enviar correo al usuario asociado a la notificación
            Mail::to($notification->user->email)->send(new NotificationMail($notification));
            
            \Log::info("Correo enviado exitosamente", ['notification_id' => $notification->id]);
        } catch (\Exception $e) {
            // Log del error con stack trace
            \Log::error('Error enviando correo de notificación', [
                'notification_id' => $notification->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
