<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'correo_electronico'
    ];

    /**
     * Obtiene las ventas del cliente
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

    /**
     * Obtiene el total de compras del cliente
     */
    public function getTotalComprasAttribute()
    {
        return $this->ventas()->sum('total');
    }

    /**
     * Obtiene el número total de ventas del cliente
     */
    public function getNumeroVentasAttribute()
    {
        return $this->ventas()->count();
    }

    /**
     * Obtiene la última venta del cliente
     */
    public function getUltimaVentaAttribute()
    {
        return $this->ventas()->latest()->first();
    }

    /**
     * Obtiene las ventas recientes del cliente (últimas 5)
     */
    public function getVentasRecientesAttribute()
    {
        return $this->ventas()->with(['detalles.producto'])->latest()->limit(5)->get();
    }

    /**
     * Obtiene el estado de fidelización del cliente
     */
    public function getEstadoFidelizacionAttribute()
    {
        $totalCompras = $this->getTotalComprasAttribute();

        if ($totalCompras >= 1000) {
            return 'Premium';
        } elseif ($totalCompras >= 500) {
            return 'Oro';
        } elseif ($totalCompras >= 100) {
            return 'Plata';
        } else {
            return 'Bronce';
        }
    }
}
