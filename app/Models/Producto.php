<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_venta',
        'id_categoria',
        'id_marca',
        'img_url'
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
    ];

    /**
     * Obtiene la categoría del producto
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    /**
     * Obtiene la marca del producto
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }

    /**
     * Obtiene los detalles de venta del producto
     */
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto');
    }

    /**
     * Obtiene los detalles de compra del producto
     */
    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }

    /**
     * Obtiene el número total de ventas del producto
     */
    public function getTotalVentasAttribute()
    {
        return $this->detallesVenta()->sum('cantidad');
    }

    /**
     * Obtiene el total de ingresos por ventas del producto
     */
    public function getTotalIngresosAttribute()
    {
        return $this->detallesVenta()->sum('subtotal');
    }

    /**
     * Obtiene el stock disponible del producto (suma de compras - suma de ventas)
     */
    public function getStockDisponibleAttribute()
    {
        $totalCompras = $this->detallesCompra()->sum('cantidad');
        $totalVentas = $this->detallesVenta()->sum('cantidad');
        return $totalCompras - $totalVentas;
    }

    /**
     * Obtiene el estado dinámico del producto basado en el stock disponible
     */
    public function getEstadoDisponibleAttribute()
    {
        $stock = $this->stock_disponible;
        return $stock > 0 ? 'disponible' : 'agotado';
    }

    /**
     * Obtiene el stock disponible del producto (suma de compras)
     */
    public function getStockTotalAttribute()
    {
        return $this->detallesCompra()->sum('cantidad');
    }
}
