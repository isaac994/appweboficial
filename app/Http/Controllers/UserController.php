<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(request('estado'), function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when(request('rol'), function ($query, $rol) {
                $query->whereHas('roles', function ($q) use ($rol) {
                    $q->where('name', $rol);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => request()->only(['search', 'estado', 'rol'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'estado' => 'required|in:activo,inactivo',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'estado' => $request->estado,
        ]);

        $user->assignRole($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('Users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'estado' => 'required|in:activo,inactivo',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'estado' => $request->estado,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Cambiar estado del usuario (activar/desactivar)
     */
    public function toggleStatus(User $user)
    {
        if ($user->estado === 'activo') {
            $user->desactivar();
            $message = 'Usuario desactivado exitosamente.';
        } else {
            $user->activar();
            $message = 'Usuario activado exitosamente.';
        }

        return redirect()->route('users.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     * Nota: No eliminamos usuarios, solo los desactivamos
     */
    public function destroy(User $user)
    {
        // No permitir eliminar el usuario actual
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $user->desactivar();

        return redirect()->route('users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }
}
