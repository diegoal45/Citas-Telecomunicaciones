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

        // Resumen para dashboard sin cargar lista completa
        if (request()->boolean('summary')) {
            $allUsers = User::with('role')->get();
            $byRole = [
                'admin' => 0,
                'tecnico_lider' => 0,
                'tecnico' => 0,
                'cliente' => 0,
            ];

            foreach ($allUsers as $user) {
                $roleName = optional($user->role)->name;
                if (isset($byRole[$roleName])) {
                    $byRole[$roleName]++;
                }
                if ($roleName === 'user') {
                    $byRole['cliente']++;
                }
            }

            return response()->json([
                'total' => $allUsers->count(),
                'by_role' => $byRole,
            ]);
        }

        $search = request()->query('search');
        $roleId = request()->query('role_id');
        $perPage = (int) request()->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        $query = User::with(['role', 'teams:id,name', 'ledTeams:id,name,leader_id'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($roleId, function ($q) use ($roleId) {
                $q->where('id_rol', $roleId);
            })
            ->orderBy('name');

        return response()->json($query->paginate($perPage));
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
        $user = User::with(['role', 'teams:id,name', 'ledTeams:id,name,leader_id'])->findOrFail($id);

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
        $authUser = Auth::user();

        // El usuario solo puede actualizar su propio perfil, a menos que sea admin
        if (Auth::id() !== (int)$id && $authUser->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validated();

        // Regla de seguridad: ningun usuario puede cambiar su propio rol
        if ((int) Auth::id() === (int) $id && array_key_exists('id_rol', $data)) {
            if ((int) $data['id_rol'] !== (int) $user->id_rol) {
                return response()->json(['message' => 'No puedes cambiarte tu propio rol'], 403);
            }
        }

        // Solo admin puede modificar el rol de terceros
        if ($authUser->role->name !== 'admin') {
            unset($data['id_rol']);
        }

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user->load(['role', 'teams:id,name', 'ledTeams:id,name,leader_id']));
    }

    /**
     * Elimina un usuario (solo admins).
     */
    public function destroy($id)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ((int) Auth::id() === (int) $id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
