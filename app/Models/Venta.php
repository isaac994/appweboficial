<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'fecha',
        'id_cliente',
        'id_usuario',
        'estado',
        'fecha_eliminacion'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
        'fecha_eliminacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Los accessors que se incluyen en la serialización JSON
     */
    protected $appends = ['total'];

    /**
     * Obtiene el cliente de la venta
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Obtiene el usuario que realizó la venta
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtiene los detalles de la venta
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }

    /**
     * Obtiene el total de productos en la venta
     */
    public function getTotalProductosAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    /**
     * Obtiene el total de la venta calculado dinámicamente
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
