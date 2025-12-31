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
     * Obtiene los modelos de esta marca
     */
    public function modelos(): HasMany
    {
        return $this->hasMany(Modelo::class, 'id_marca', 'id_marca');
    }

    /**
     * Obtiene los productos de esta marca a través de los modelos
     */
    public function productos()
    {
        return $this->hasManyThrough(
            Producto::class,
            Modelo::class,
            'id_marca', // Foreign key en modelos
            'id_modelo', // Foreign key en productos
            'id_marca', // Local key en marcas
            'id_modelo' // Local key en modelos
        );
    }
}
