<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\NotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestNotificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-notification {--email=} {--notification-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un correo de notificación de prueba';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $notificationId = $this->option('notification-id');

        if ($notificationId) {
            // Enviar notificación existente
            $notification = Notification::find($notificationId);
            
            if (!$notification) {
                $this->error("Notificación con ID {$notificationId} no encontrada");
                return Command::FAILURE;
            }

            $this->info("Enviando notificación: {$notification->title}");
            
            try {
                Mail::to($notification->user->email)->send(new NotificationMail($notification));
                $this->info("✓ Correo enviado exitosamente a {$notification->user->email}");
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("Error al enviar correo: {$e->getMessage()}");
                return Command::FAILURE;
            }
        }

        if ($email) {
            // Enviar una notificación de prueba
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("Usuario con email {$email} no encontrado");
                return Command::FAILURE;
            }

            // Crear una notificación de prueba
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => 'test',
                'title' => '[TEST] Esto es una prueba de correo',
                'message' => 'Este es un correo de prueba para verificar que la configuración de Gmail está funcionando correctamente.',
                'data' => json_encode(['timestamp' => now()->toDateTimeString()]),
                'is_read' => false
            ]);

            $this->info("Enviando correo de prueba a {$email}...");
            
            try {
                Mail::to($email)->send(new NotificationMail($notification));
                $this->info("✓ Correo de prueba enviado exitosamente a {$email}");
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("Error al enviar correo: {$e->getMessage()}");
                return Command::FAILURE;
            }
        }

        // Si no se proporciona email ni notification-id, mostrar instrucciones
        $this->info("Uso:");
        $this->line("  php artisan email:test-notification --email=usuario@example.com");
        $this->line("  php artisan email:test-notification --notification-id=1");
        $this->line("");
        $this->info("Opciones:");
        $this->line("  --email              Enviar correo de prueba a un email");
        $this->line("  --notification-id    Reenviar una notificación existente");

        return Command::SUCCESS;
    }
}
