<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_venta',
        'id_categoria',
        'id_modelo',
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
     * Obtiene la marca del producto a través del modelo
     */
    public function marca()
    {
        return $this->hasOneThrough(
            Marca::class,
            Modelo::class,
            'id_modelo', // Foreign key en modelos que apunta a productos
            'id_marca', // Foreign key en marcas que apunta a modelos
            'id_modelo', // Local key en productos
            'id_marca' // Local key en modelos
        );
    }

    /**
     * Obtiene el modelo del producto
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class, 'id_modelo');
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
     * Excluye las compras eliminadas (estado = true)
     */
    public function getStockDisponibleAttribute()
    {
        $totalCompras = $this->detallesCompra()
            ->whereHas('compra', function ($query) {
                $query->where('estado', false); // Solo compras activas (no eliminadas)
            })
            ->sum('cantidad');
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
     * Obtiene el stock total del producto (suma de compras activas)
     * Excluye las compras eliminadas (estado = true)
     */
    public function getStockTotalAttribute()
    {
        return $this->detallesCompra()
            ->whereHas('compra', function ($query) {
                $query->where('estado', false); // Solo compras activas (no eliminadas)
            })
            ->sum('cantidad');
    }

    /**
     * Obtiene el último precio de compra del producto (solo compras activas)
     * Ordena por fecha de compra descendente y luego por ID de compra descendente
     * para asegurar que obtiene la compra más reciente
     */
    public function getUltimoPrecioCompraAttribute()
    {
        $ultimaCompra = DB::table('detalle_compras')
            ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
            ->where('detalle_compras.id_producto', $this->id_producto)
            ->where('compras.estado', false) // Solo compras activas (no eliminadas)
            ->orderBy('compras.fecha', 'desc')
            ->orderBy('compras.id_compra', 'desc') // Ordenar también por ID para asegurar el más reciente
            ->select('detalle_compras.precio_unitario')
            ->first();

        return $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;
    }
}
