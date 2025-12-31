<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaticRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('apellidos', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('ci', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('estado', 'like', "%{$search}%")
                      ->orWhere('rol', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Asegurar que los roles se incluyan en la respuesta
        $users->getCollection()->transform(function ($user) {
            return $user->append(['roles', 'permissions']);
        });

        $roles = StaticRole::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => request()->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = StaticRole::all();

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
            'name' => 'required|string|max:30',
            'apellidos' => 'nullable|string|max:30',
            'email' => 'required|string|email|max:30|unique:users,email',
            'telefono' => 'nullable|string|max:30',
            'ci' => 'required|string|max:30|unique:users,ci',
            'direccion' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,inactivo',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:Propietario,Operador'
        ], [
            'ci.unique' => 'El número de Cédula de Identidad ya está registrado.',
            'email.unique' => 'El correo electrónico ya está registrado.'
        ]);

        // Obtener el primer rol seleccionado
        $firstRole = $request->roles[0];

        // Usar el CI como contraseña automáticamente
        $password = $request->ci;

        $user = User::create([
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => Hash::make($password),
            'telefono' => $request->telefono,
            'ci' => $request->ci,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
            'rol' => $firstRole,
        ]);

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = StaticRole::all();

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
            'name' => 'required|string|max:30',
            'apellidos' => 'nullable|string|max:30',
            'email' => ['required', 'string', 'email', 'max:30', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:30',
            'ci' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'direccion' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,inactivo',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:Propietario,Operador'
        ]);

        // Prevenir que un usuario se desactive a sí mismo
        if ($user->id === auth()->id() && $request->estado === 'inactivo') {
            return redirect()->back()
                ->withErrors(['estado' => 'No puedes desactivarte a ti mismo.'])
                ->withInput();
        }

        // Obtener el primer rol seleccionado
        $firstRole = $request->roles[0];

        $user->update([
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'ci' => $request->ci,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
            'rol' => $firstRole,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Cambiar estado del usuario (activar/desactivar)
     */
    public function toggleStatus(User $user)
    {
        // Prevenir que un usuario se desactive a sí mismo
        if ($user->id === auth()->id() && $user->estado === 'activo') {
            return redirect()->route('users.index')
                ->with('error', 'No puedes desactivarte a ti mismo.');
        }

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
     * Restablecer contraseña del usuario a su CI
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:1'
        ]);

        // Verificar si la contraseña que se intenta establecer ya es la actual
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña ya está establecida con este valor. No es necesario restablecerla.',
                'already_set' => true
            ], 200); // 200 porque técnicamente no es un error, solo información
        }

        // Actualizar la contraseña del usuario
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Retornar respuesta JSON en lugar de redirect con sesión
        return response()->json([
            'success' => true,
            'message' => "Contraseña restablecida exitosamente. La nueva contraseña es: {$request->password}"
        ]);
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
