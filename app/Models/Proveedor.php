<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'correo'
    ];

    /**
     * Obtiene los productos de este proveedor
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_proveedor');
    }

    /**
     * Obtiene las compras realizadas a este proveedor
     */
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'id_proveedor');
    }
}
