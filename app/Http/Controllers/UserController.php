<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios (solo admins).
     */
    public function index()
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $users = User::with('role')->get();
        return response()->json($users);
    }

    /**
     * Crea un nuevo usuario (solo admins).
     */
    public function store(StoreUserRequest $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user->load('role'), 201);
    }

    /**
     * Muestra información del usuario (el usuario ve solo su perfil, admin ve cualquiera).
     */
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        // El usuario solo puede ver su propio perfil, a menos que sea admin
        if (Auth::id() !== (int)$id && Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($user);
    }

    /**
     * Actualiza datos de un usuario (el usuario actualiza su propio perfil, admin actualiza cualquiera).
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // El usuario solo puede actualizar su propio perfil, a menos que sea admin
        if (Auth::id() !== (int)$id && Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validated();

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user->load('role'));
    }

    /**
     * Elimina un usuario (solo admins).
     */
    public function destroy($id)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
