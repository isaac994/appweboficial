<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Credenciales del usuario admin por defecto (hardcoded)
     */
    private function getDefaultAdminCredentials()
    {
        return [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'name' => 'Administrador',
            'rol' => 'Propietario',
            'estado' => 'activo'
        ];
    }

    /**
     * Verificar si las credenciales corresponden al usuario admin por defecto
     */
    private function isDefaultAdmin($email, $password)
    {
        $admin = $this->getDefaultAdminCredentials();
        // Comparar con trim para evitar problemas con espacios y case-insensitive para email
        $emailMatch = strtolower(trim($email)) === strtolower(trim($admin['email']));
        $passwordMatch = trim($password) === trim($admin['password']);
        
        return $emailMatch && $passwordMatch;
    }

    /**
     * Crear un objeto usuario virtual para el admin por defecto
     */
    private function createDefaultAdminUser()
    {
        return User::getDefaultAdmin();
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = trim($request->input('email'));
        $password = trim($request->input('password'));
        $remember = $request->boolean('remember');

        // Verificar PRIMERO si es el usuario admin por defecto (antes de cualquier consulta a BD)
        $admin = $this->getDefaultAdminCredentials();
        $isAdmin = (strtolower($email) === strtolower($admin['email']) && $password === $admin['password']);

        if ($isAdmin) {
            $adminUser = $this->createDefaultAdminUser();
            
            // Autenticar manualmente al usuario admin
            Auth::guard('web')->login($adminUser, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', '¡Bienvenido de vuelta!');
        }

        // Si no es el admin por defecto, intentar autenticación normal con BD
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', '¡Bienvenido de vuelta!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->only('email'));
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Procesar registro
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verificar si es el primer usuario (no hay usuarios en la BD)
        $isFirstUser = User::count() === 0;
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $isFirstUser ? 'Propietario' : 'Operador', // Primer usuario es Propietario, los demás Operador
            'estado' => 'activo',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', '¡Cuenta creada exitosamente!');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Has cerrado sesión correctamente');
    }

    /**
     * Mostrar perfil del usuario
     */
    public function profile()
    {
        return Inertia::render('Auth/Profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'email.unique' => 'Este email ya está registrado'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'password.required' => 'La nueva contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Contraseña cambiada correctamente');
    }
}
