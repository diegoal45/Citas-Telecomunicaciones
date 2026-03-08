<?php

namespace App\Observers;

use App\Models\Notification;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     * Envía un correo cuando se crea una notificación
     */
    public function created(Notification $notification): void
    {
        try {
            // Ejecutar envio despues de responder al cliente para no bloquear el API.
            dispatch(function () use ($notification): void {
                try {
                    $notification->loadMissing('user');
                    $email = $notification->user?->email;

                    if (!$email) {
                        Log::warning('Notificacion sin email de destino', [
                            'notification_id' => $notification->id,
                            'user_id' => $notification->user_id,
                        ]);
                        return;
                    }

                    Mail::to($email)->send(new NotificationMail($notification));
                } catch (\Throwable $e) {
                    Log::error('Error enviando correo de notificacion', [
                        'notification_id' => $notification->id ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        } catch (\Exception $e) {
            Log::error('Error programando correo de notificacion', [
                'notification_id' => $notification->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
