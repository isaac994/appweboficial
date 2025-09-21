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
        'descripcion'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2'
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
     * Obtiene el total parcial calculado dinámicamente
     */
    public function getTotalParcialAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
