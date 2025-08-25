<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle_venta';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'total_parcial'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'total_parcial' => 'decimal:2'
    ];

    /**
     * Obtiene la venta del detalle
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    /**
     * Obtiene el producto del detalle
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    /**
     * Calcula el total parcial automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->total_parcial = $detalle->cantidad * $detalle->precio_unitario;
        });
    }
}
