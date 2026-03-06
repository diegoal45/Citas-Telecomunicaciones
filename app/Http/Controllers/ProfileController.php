<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user->load('role')
        ]);
    }

    public function update()
    {
        $request = request();
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255'
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->load('role')
        ]);
    }

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

    public function updatePassword()
    {
        $request = request();
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contraseña actual es incorrecta'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
}
