<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class DefaultAdminUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // Si el ID es 0, devolver el usuario admin por defecto
        if ($identifier == 0 || $identifier === '0') {
            return User::getDefaultAdmin();
        }

        // Para otros usuarios, usar el método padre
        return parent::retrieveById($identifier);
    }
}

