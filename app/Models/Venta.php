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
        'total',
        'id_cliente',
        'id_usuario'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

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
}
