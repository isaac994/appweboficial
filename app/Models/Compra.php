<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'id_proveedor',
        'id_usuario',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Los accessors que se incluyen en la serialización JSON
     */
    protected $appends = ['total'];

    /**
     * Obtiene el proveedor de la compra
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    /**
     * Obtiene el usuario que realizó la compra
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtiene los detalles de la compra
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }

    /**
     * Obtiene el total de productos en la compra
     */
    public function getTotalProductosAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    /**
     * Obtiene el total de la compra calculado dinámicamente
     */
    public function getTotalAttribute()
    {
        // Asegurar que los detalles estén cargados
        if (!$this->relationLoaded('detalles')) {
            $this->load('detalles');
        }
        
        $total = $this->detalles->sum(function ($detalle) {
            return floatval($detalle->cantidad) * floatval($detalle->precio_unitario);
        });
        
        return round($total, 2);
    }
}
