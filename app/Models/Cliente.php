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
        'apellidos',
        'ci',
        'telefono'
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
        return $this->ventas()->with('detalles')->get()->sum(function($venta) {
            return $venta->detalles->sum('total_parcial');
        });
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
}
