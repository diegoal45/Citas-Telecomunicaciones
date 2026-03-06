<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function uploadPhoto()
    {
        $request = request();
        
        if (!$request->hasFile('photo')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $user = Auth::user();
        
        // Eliminar foto anterior si existe
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Guardar nueva foto
        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return response()->json([
            'message' => 'Foto actualizada correctamente',
            'profile_photo_path' => $path,
            'profile_photo_url' => Storage::disk('public')->url($path)
        ]);
    }

    public function getPhoto()
    {
        $user = Auth::user();
        
        if (!$user->profile_photo_path) {
            return response()->json(['photo_url' => null]);
        }

        return response()->json([
            'photo_url' => Storage::disk('public')->url($user->profile_photo_path)
        ]);
    }
}
