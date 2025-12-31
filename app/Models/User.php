<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Boot del modelo - interceptar intentos de actualizar remember_token
     */
    protected static function boot()
    {
        parent::boot();

        // Interceptar antes de guardar para eliminar remember_token de los atributos
        static::saving(function ($user) {
            if (isset($user->attributes['remember_token'])) {
                unset($user->attributes['remember_token']);
            }
            if (isset($user->original['remember_token'])) {
                unset($user->original['remember_token']);
            }
        });

        // Interceptar consultas where('id', 0) para devolver el usuario admin
        static::addGlobalScope('default_admin', function ($builder) {
            // No hacer nada aquí, solo registrar el scope
        });
    }

    /**
     * Obtener credenciales del usuario admin por defecto
     */
    private static function getDefaultAdminCredentials()
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
     * Crear el usuario admin por defecto
     */
    public static function getDefaultAdmin()
    {
        $admin = self::getDefaultAdminCredentials();
        
        $user = new static();
        $user->id = 0;
        $user->name = $admin['name'];
        $user->email = $admin['email'];
        $user->rol = $admin['rol'];
        $user->estado = $admin['estado'];
        $user->password = \Illuminate\Support\Facades\Hash::make($admin['password']);
        $user->exists = true;
        $user->wasRecentlyCreated = false;
        
        // Asegurar que el getAuthIdentifier devuelva 0
        $user->setRawAttributes(['id' => 0], true);
        
        return $user;
    }

    /**
     * Sobrescribir el método find para devolver el usuario admin cuando el ID sea 0
     */
    public static function find($id, $columns = ['*'])
    {
        // Manejar el caso del usuario admin por defecto
        if ($id === 0 || $id === '0' || (is_string($id) && trim($id) === '0')) {
            return self::getDefaultAdmin();
        }
        
        // Si el ID es null, devolver null
        if ($id === null) {
            return null;
        }
        
        return parent::find($id, $columns);
    }

    /**
     * Sobrescribir findOrFail para manejar el usuario admin por defecto
     */
    public static function findOrFail($id, $columns = ['*'])
    {
        // Manejar el caso del usuario admin por defecto
        if ($id === 0 || $id === '0' || (is_string($id) && trim($id) === '0')) {
            return self::getDefaultAdmin();
        }
        
        return parent::findOrFail($id, $columns);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellidos',
        'email',
        'password',
        'telefono',
        'ci',
        'direccion',
        'estado',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['roles', 'permissions'];

    /**
     * Scope para usuarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para usuarios inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'inactivo');
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActivo()
    {
        return $this->estado === 'activo';
    }

    /**
     * Activar usuario
     */
    public function activar()
    {
        $this->update(['estado' => 'activo']);
    }

    /**
     * Desactivar usuario
     */
    public function desactivar()
    {
        $this->update(['estado' => 'inactivo']);
    }

    /**
     * Obtener el rol del usuario
     */
    public function getRoleAttribute()
    {
        return $this->attributes['rol'] ?? null;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role)
    {
        $userRol = $this->rol ?? $this->attributes['rol'] ?? null;
        return $userRol === $role;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole($roles)
    {
        return in_array($this->rol, (array) $roles);
    }

    /**
     * Asignar un rol al usuario
     */
    public function assignRole($role)
    {
        if (StaticRole::exists($role)) {
            $this->update(['rol' => $role]);
        }
    }

    /**
     * Sincronizar roles (mantener compatibilidad con Spatie)
     */
    public function syncRoles($roles)
    {
        if (is_array($roles) && count($roles) > 0) {
            $this->assignRole($roles[0]); // Solo tomar el primer rol
        }
    }

    /**
     * Obtener los permisos del usuario basados en su rol
     */
    public function getPermissions()
    {
        if (!$this->rol) {
            return [];
        }
        return StaticRole::getPermissions($this->rol);
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermissionTo($permission)
    {
        if (!$this->rol) {
            return false;
        }
        $permissions = $this->getPermissions();
        return in_array($permission, $permissions);
    }

    /**
     * Obtener el rol como objeto (para compatibilidad con el frontend)
     */
    public function getRolesAttribute()
    {
        if (!$this->rol) {
            return [];
        }

        $role = StaticRole::findByName($this->rol);
        return $role ? [$role] : [];
    }

    /**
     * Obtener los permisos como array (para compatibilidad con el frontend)
     */
    public function getPermissionsAttribute()
    {
        return $this->getPermissions();
    }

    /**
     * Sobrescribir el método para deshabilitar remember_token
     * Esto evita que Laravel intente guardar remember_token en la base de datos
     * Devolvemos un string vacío en lugar de null para evitar errores
     */
    public function getRememberTokenName()
    {
        return '';
    }

    /**
     * Sobrescribir para evitar que se intente obtener remember_token
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Sobrescribir para evitar que se intente guardar remember_token
     * Laravel intentará actualizar esta columna, pero al devolver un string vacío
     * como nombre de columna, no intentará hacer el UPDATE
     */
    public function setRememberToken($value)
    {
        // No hacer nada - no guardamos remember_token en la base de datos
        // La funcionalidad de "recordar sesión" seguirá funcionando mediante cookies de sesión
        return $this;
    }
}
