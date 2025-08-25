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
        'fecha',
        'total'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

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
}
