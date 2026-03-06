<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        $settings = [
            'notifications_enabled' => $user->notifications_enabled ?? true,
            'email_notifications_enabled' => $user->email_notifications_enabled ?? true,
            'dark_mode' => $user->dark_mode ?? false,
            'timezone' => $user->timezone ?? 'America/Bogota'
        ];

        return response()->json([
            'settings' => $settings
        ]);
    }

    public function update()
    {
        $request = request();
        $user = Auth::user();

        $data = $request->validate([
            'notifications_enabled' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'dark_mode' => 'boolean',
            'timezone' => 'nullable|string|max:50'
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Configuración guardada correctamente',
            'settings' => $data
        ]);
    }
}
