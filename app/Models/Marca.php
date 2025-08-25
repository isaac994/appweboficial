<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    protected $primaryKey = 'id_marca';

    protected $fillable = [
        'nombre',
        'pais_origen'
    ];

    /**
     * Obtiene los productos de esta marca
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_marca');
    }
}
