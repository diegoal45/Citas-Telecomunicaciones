<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios.
     */
    public function index()
    {
        $users = User::with('role')->get();
        return response()->json($users);
    }

    /**
     * Crea un nuevo usuario (puede ser usado por un administrador).
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user->load('role'), 201);
    }

    /**
     * Muestra la información de un usuario en particular.
     */
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Actualiza datos de un usuario existente.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user->load('role'));
    }

    /**
     * Elimina un usuario.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
