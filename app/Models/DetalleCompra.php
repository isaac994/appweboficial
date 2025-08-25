<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    protected $primaryKey = 'id_detalle_compra';

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'total_parcial'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'total_parcial' => 'decimal:2',
    ];

    /**
     * Obtiene la compra a la que pertenece este detalle
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra');
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
