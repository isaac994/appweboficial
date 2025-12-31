<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\DetalleCompra;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    /**
     * Muestra el inventario completo con entradas, salidas y stock
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $fecha_final = $request->input('fecha_final', '');
        $stock_min = $request->input('stock_min', '');
        $stock_max = $request->input('stock_max', '');

        // Obtener todos los productos con sus relaciones
        $query = Producto::with(['categoria', 'marca', 'modelo'])
            ->select('productos.*');

        // Aplicar filtros de búsqueda - NO filtrar aquí, se filtra después de mapear
        // Los productos sin compras/ventas también deben aparecer con valores en 0


        $productos = $query->get();

        // Validar y normalizar la fecha si existe
        $fechaFiltro = null;
        if ($fecha_final && trim($fecha_final) !== '') {
            try {
                $fechaFiltro = Carbon::parse($fecha_final)->format('Y-m-d');
            } catch (\Exception $e) {
                $fechaFiltro = null;
            }
        }

        // Para cada producto, calcular entradas, salidas y stock
        $inventario = $productos->map(function ($producto) use ($fechaFiltro) {
            $driver = DB::connection()->getDriverName();

            // Construir query base para entradas - excluir compras eliminadas (estado = false)
            $entradasQuery = DetalleCompra::join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->where('detalle_compras.id_producto', $producto->id_producto)
                ->where('compras.estado', false);

            // Aplicar filtro de fecha si existe
            if ($fechaFiltro !== null) {
                if ($driver === 'sqlite') {
                    $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) <= ?", [$fechaFiltro]);
                } else {
                    $entradasQuery->whereRaw("DATE(compras.fecha) <= ?", [$fechaFiltro]);
                }
            }

            $entradas = $entradasQuery->select(
                DB::raw('SUM(detalle_compras.cantidad) as total_cantidad'),
                DB::raw('SUM(detalle_compras.cantidad * detalle_compras.precio_unitario) as total_costo')
            )->first();

            // Construir query base para salidas - excluir ventas eliminadas (estado = false)
            $salidasQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                ->where('detalle_ventas.id_producto', $producto->id_producto)
                ->where('ventas.estado', false);

            // Aplicar filtro de fecha si existe
            if ($fechaFiltro !== null) {
                if ($driver === 'sqlite') {
                    $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) <= ?", [$fechaFiltro]);
                } else {
                    $salidasQuery->whereRaw("DATE(ventas.fecha) <= ?", [$fechaFiltro]);
                }
            }

            $salidas = $salidasQuery->select(
                DB::raw('SUM(detalle_ventas.cantidad) as total_cantidad'),
                DB::raw('SUM(detalle_ventas.cantidad * detalle_ventas.precio_unitario) as total_ingresos')
            )->first();

            $total_entradas = floatval($entradas->total_cantidad ?? 0);
            $total_salidas = floatval($salidas->total_cantidad ?? 0);
            $stock_actual = $total_entradas - $total_salidas;

            $costo_total = floatval($entradas->total_costo ?? 0);
            $ingresos_total = floatval($salidas->total_ingresos ?? 0);

            // Costo unitario promedio - evitar división por cero
            $costo_unitario = $total_entradas > 0 ? $costo_total / $total_entradas : 0;

            // Obtener el precio de la última compra realizada del producto (hasta la fecha filtrada si existe)
            $ultimaCompraQuery = DB::table('detalle_compras')
                ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->where('detalle_compras.id_producto', $producto->id_producto)
                ->where('compras.estado', false); // Solo compras activas

            if ($fechaFiltro !== null) {
                if ($driver === 'sqlite') {
                    $ultimaCompraQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) <= ?", [$fechaFiltro]);
                } else {
                    $ultimaCompraQuery->whereRaw("DATE(compras.fecha) <= ?", [$fechaFiltro]);
                }
            }

            $ultimaCompra = $ultimaCompraQuery->orderBy('compras.fecha', 'desc')
                ->orderBy('compras.id_compra', 'desc') // Ordenar también por ID para asegurar el más reciente
                ->select('detalle_compras.precio_unitario')
                ->first();

            $precio_ultima_compra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;

            // Calcular ganancia real sumando las ganancias de cada venta individual
            // Para cada venta, se usa el último precio de compra hasta la fecha de esa venta
            $ganancia_bruta = 0;
            if ($total_salidas > 0) {
                // Obtener todos los detalles de venta del producto
                $detallesVentaQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $producto->id_producto)
                    ->where('ventas.estado', false)
                    ->select('detalle_ventas.*', 'ventas.fecha as fecha_venta');

                // Aplicar filtro de fecha si existe
                if ($fechaFiltro !== null) {
                    if ($driver === 'sqlite') {
                        $detallesVentaQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) <= ?", [$fechaFiltro]);
                    } else {
                        $detallesVentaQuery->whereRaw("DATE(ventas.fecha) <= ?", [$fechaFiltro]);
                    }
                }

                $detallesVenta = $detallesVentaQuery->get();

                // Para cada detalle de venta, calcular su ganancia usando el último precio de compra hasta esa fecha
                foreach ($detallesVenta as $detalle) {
                    $fechaVenta = $detalle->fecha_venta;
                    $precioVenta = floatval($detalle->precio_unitario);
                    $cantidad = floatval($detalle->cantidad);

                    // Buscar el último precio de compra hasta la fecha de esta venta
                    $ultimaCompraQuery = DB::table('detalle_compras')
                        ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                        ->where('detalle_compras.id_producto', $producto->id_producto)
                        ->where('compras.estado', false);

                    if ($driver === 'sqlite') {
                        $ultimaCompraQuery->whereRaw("datetime(compras.fecha) <= datetime(?)", [$fechaVenta]);
                    } else {
                        $ultimaCompraQuery->where('compras.fecha', '<=', $fechaVenta);
                    }

                    $ultimaCompra = $ultimaCompraQuery->orderBy('compras.fecha', 'desc')
                        ->orderBy('compras.id_compra', 'desc')
                        ->select('detalle_compras.precio_unitario')
                        ->first();

                    $precioCompra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;

                    // Si no hay precio de compra, usar el costo unitario promedio como fallback
                    if ($precioCompra == 0 && $costo_unitario > 0) {
                        $precioCompra = $costo_unitario;
                    }

                    // Calcular ganancia de este detalle: (precio_venta - precio_compra) * cantidad
                    $gananciaDetalle = ($precioVenta - $precioCompra) * $cantidad;
                    $ganancia_bruta += $gananciaDetalle;
                }
            }

            // Calcular ganancia por unidad promedio (solo para referencia)
            $ganancia_por_unidad = $total_salidas > 0 ? ($ganancia_bruta / $total_salidas) : 0;

            // Ganancia potencial del stock actual
            $ganancia_potencial_stock = $stock_actual * ($producto->precio_venta - $costo_unitario);

            // Calcular valor de stock usando el precio de la última compra disponible
            $ultimoPrecio = $precio_ultima_compra > 0 ? $precio_ultima_compra : $costo_unitario;
            $valor_stock = $stock_actual * $ultimoPrecio;

            // Validar que todos los valores sean números válidos
            $costo_total = is_numeric($costo_total) ? $costo_total : 0;
            $ingresos_total = is_numeric($ingresos_total) ? $ingresos_total : 0;
            $ganancia_bruta = is_numeric($ganancia_bruta) ? $ganancia_bruta : 0;
            $costo_unitario = is_numeric($costo_unitario) ? $costo_unitario : 0;
            $valor_stock = is_numeric($valor_stock) ? $valor_stock : 0;

            return [
                'id_producto' => $producto->id_producto,
                'producto' => $producto->descripcion,
                'modelo' => $producto->modelo?->nombre ?? 'N/A',
                'marca' => $producto->marca?->nombre ?? 'N/A',
                'categoria' => $producto->categoria?->nombre ?? 'N/A',
                'total_entradas' => $total_entradas,
                'costo_total_entradas' => $costo_total,
                'costo_unitario' => $costo_unitario,
                'total_salidas' => $total_salidas,
                'ingresos_totales' => $ingresos_total,
                'ganancia_bruta' => $ganancia_bruta,
                'ganancia_por_unidad' => $ganancia_por_unidad,
                'ganancia_potencial_stock' => $ganancia_potencial_stock,
                'stock_actual' => $stock_actual,
                'valor_stock' => $valor_stock,
                'precio_venta' => $producto->precio_venta,
                'precio_venta_promedio' => $total_salidas > 0 ? $ingresos_total / $total_salidas : 0,
            ];
        });

        // Si hay filtro de fecha, mostrar solo productos con movimientos hasta esa fecha
        if ($fechaFiltro !== null) {
            $inventario = $inventario->filter(function ($item) {
                // Mostrar solo productos que tienen movimientos (entradas, salidas o stock)
                return ($item['total_entradas'] > 0 || $item['total_salidas'] > 0 || $item['stock_actual'] != 0);
            })->values();
        }

        // Aplicar filtro de búsqueda después de mapear
        if ($search && trim($search) !== '') {
            $searchLower = mb_strtolower(trim($search));
            $inventario = $inventario->filter(function ($item) use ($searchLower) {
                $producto = mb_strtolower($item['producto'] ?? '');
                $modelo = mb_strtolower($item['modelo'] ?? '');
                $marca = mb_strtolower($item['marca'] ?? '');
                $categoria = mb_strtolower($item['categoria'] ?? '');

                return
                    stripos($producto, $searchLower) !== false ||
                    stripos($modelo, $searchLower) !== false ||
                    stripos($marca, $searchLower) !== false ||
                    stripos($categoria, $searchLower) !== false;
            })->values();
        }

        // Aplicar filtros por rango de stock
        // Solo aplicar si los valores son diferentes de vacío y no son strings vacíos
        if (($stock_min !== '' && $stock_min !== null && $stock_min !== '0') || ($stock_max !== '' && $stock_max !== null && $stock_max !== '∞')) {
            $inventario = $inventario->filter(function ($item) use ($stock_min, $stock_max) {
                $stock = $item['stock_actual'];

                if ($stock_min !== '' && $stock_min !== null && $stock_min !== '0') {
                    if ($stock < (float)$stock_min) {
                        return false;
                    }
                }

                if ($stock_max !== '' && $stock_max !== null && $stock_max !== '∞') {
                    if ($stock > (float)$stock_max) {
                        return false;
                    }
                }

                return true;
            })->values();
        }

        // Obtener categorías, marcas y modelos para filtros
        $categorias = \App\Models\Categoria::orderBy('nombre')->get();
        $marcas = \App\Models\Marca::orderBy('nombre')->get();
        $modelos = \App\Models\Modelo::orderBy('nombre')->get();

        // Convertir a Array
        $inventarioArray = $inventario->toArray();

        // Log para debug
        \Log::info('Inventario generado', [
            'total_items' => count($inventarioArray),
            'filtro_busqueda' => $search,
            'primer_item' => $inventarioArray[0] ?? null,
        ]);

        return Inertia::render('Inventario/Index', [
            'inventario' => $inventarioArray,
            'productos' => null, // Sin paginación por ahora
            'categorias' => $categorias,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'filters' => [
                'search' => $search,
                'fecha_final' => $fecha_final,
                'stock_min' => $stock_min,
                'stock_max' => $stock_max,
            ]
        ]);
    }

    /**
     * Muestra los movimientos de inventario (entradas y salidas)
     */
    public function movimientos(Request $request)
    {
        $search = $request->input('search', '');
        $tipo = $request->input('tipo', '');
        $fecha_hasta = $request->input('fecha_hasta', '');

        // Fecha desde siempre fija: 1 de enero de 2025
        $fechaDesde = '2025-01-01';

        // Validar y normalizar la fecha hasta
        $fechaHasta = null;

        if ($fecha_hasta && trim($fecha_hasta) !== '') {
            try {
                $fechaHasta = Carbon::parse($fecha_hasta)->format('Y-m-d');
            } catch (\Exception $e) {
                $fechaHasta = null;
            }
        }

        $movimientos = collect();

        // Obtener entradas (compras) - excluir compras eliminadas
        if ($tipo === '' || $tipo === 'entrada') {
            // Usar JOIN directo para mejor control
            $entradasQuery = DetalleCompra::join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->where('compras.estado', false)
                ->select('detalle_compras.*', 'compras.fecha', 'compras.id_compra as compra_id');

            // Aplicar filtros de fecha (desde siempre 2025-01-01)
            $driver = DB::connection()->getDriverName();
            if ($fechaHasta !== null) {
                // Rango desde 2025-01-01 hasta la fecha seleccionada
                if ($driver === 'sqlite') {
                    $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) BETWEEN ? AND ?", [$fechaDesde, $fechaHasta]);
                } else {
                    $entradasQuery->whereBetween(DB::raw('DATE(compras.fecha)'), [$fechaDesde, $fechaHasta]);
                }
            } else {
                // Solo filtrar desde 2025-01-01
                if ($driver === 'sqlite') {
                    $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) >= ?", [$fechaDesde]);
                } else {
                    $entradasQuery->whereRaw("DATE(compras.fecha) >= ?", [$fechaDesde]);
                }
            }

            $entradas = $entradasQuery->get();

            // Cargar relaciones después de obtener los datos
            $entradas->load([
                'producto.modelo',
                'producto.marca',
                'producto.categoria'
            ]);

            // Cargar relaciones de compra
            $compraIds = $entradas->pluck('compra_id')->unique();
            $compras = \App\Models\Compra::with(['usuario', 'proveedor'])
                ->whereIn('id_compra', $compraIds)
                ->get()
                ->keyBy('id_compra');

            $entradas = $entradas->map(function ($detalle) use ($compras) {
                $compra = $compras->get($detalle->compra_id);
                return [
                    'id' => 'E-' . $detalle->id_detalle_compra,
                    'fecha' => $detalle->fecha,
                    'documento_id' => $detalle->compra_id,
                    'tipo' => 'Entrada',
                    'tipo_class' => 'entrada',
                    'producto' => $detalle->producto->modelo->nombre ?? 'N/A',
                    'marca' => $detalle->producto->marca->nombre ?? 'N/A',
                    'categoria' => $detalle->producto->categoria->nombre ?? 'N/A',
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'total' => $detalle->cantidad * $detalle->precio_unitario,
                    'documento' => 'Compra #' . $detalle->compra_id,
                    'proveedor' => $compra->proveedor->nombre ?? 'N/A',
                    'usuario' => $compra->usuario->name ?? 'N/A',
                ];
            });

            $movimientos = $movimientos->concat($entradas);
        }

        // Obtener salidas (ventas) - excluir ventas eliminadas
        if ($tipo === '' || $tipo === 'salida') {
            // Usar JOIN directo para mejor control
            $salidasQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                ->where('ventas.estado', false)
                ->select('detalle_ventas.*', 'ventas.fecha', 'ventas.id_venta as venta_id');

            // Aplicar filtros de fecha (desde siempre 2025-01-01)
            $driver = DB::connection()->getDriverName();
            if ($fechaHasta !== null) {
                // Rango desde 2025-01-01 hasta la fecha seleccionada
                if ($driver === 'sqlite') {
                    $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) BETWEEN ? AND ?", [$fechaDesde, $fechaHasta]);
                } else {
                    $salidasQuery->whereBetween(DB::raw('DATE(ventas.fecha)'), [$fechaDesde, $fechaHasta]);
                }
            } else {
                // Solo filtrar desde 2025-01-01
                if ($driver === 'sqlite') {
                    $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) >= ?", [$fechaDesde]);
                } else {
                    $salidasQuery->whereRaw("DATE(ventas.fecha) >= ?", [$fechaDesde]);
                }
            }

            $salidas = $salidasQuery->get();

            // Cargar relaciones después de obtener los datos
            $salidas->load([
                'producto.modelo',
                'producto.marca',
                'producto.categoria'
            ]);

            // Cargar relaciones de venta
            $ventaIds = $salidas->pluck('venta_id')->unique();
            $ventas = \App\Models\Venta::with(['usuario', 'cliente'])
                ->whereIn('id_venta', $ventaIds)
                ->get()
                ->keyBy('id_venta');

            $salidas = $salidas->map(function ($detalle) use ($ventas) {
                $venta = $ventas->get($detalle->venta_id);
                return [
                    'id' => 'S-' . $detalle->id_detalle_venta,
                    'fecha' => $detalle->fecha,
                    'documento_id' => $detalle->venta_id,
                    'tipo' => 'Salida',
                    'tipo_class' => 'salida',
                    'producto' => $detalle->producto->modelo->nombre ?? 'N/A',
                    'marca' => $detalle->producto->marca->nombre ?? 'N/A',
                    'categoria' => $detalle->producto->categoria->nombre ?? 'N/A',
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'total' => $detalle->cantidad * $detalle->precio_unitario,
                    'documento' => 'Venta #' . $detalle->venta_id,
                    'cliente' => $venta->cliente->nombre ?? 'N/A',
                    'usuario' => $venta->usuario->name ?? 'N/A',
                ];
            });

            $movimientos = $movimientos->concat($salidas);
        }

        // Aplicar filtro de búsqueda por texto si existe
        if ($search && trim($search) !== '') {
            $searchLower = strtolower(trim($search));
            $movimientos = $movimientos->filter(function ($movimiento) use ($searchLower) {
                return
                    stripos($movimiento['producto'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['marca'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['categoria'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['documento'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['proveedor'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['cliente'] ?? '', $searchLower) !== false ||
                    stripos($movimiento['usuario'] ?? '', $searchLower) !== false;
            })->values();
        }

        // Ordenar por fecha descendente, y luego por ID del documento descendente (más recientes primero)
        $movimientos = $movimientos->sort(function ($a, $b) {
            // Primero comparar por fecha (descendente)
            $fechaA = strtotime($a['fecha']);
            $fechaB = strtotime($b['fecha']);
            if ($fechaB != $fechaA) {
                return $fechaB - $fechaA; // Descendente
            }
            // Si las fechas son iguales, comparar por ID del documento (descendente)
            return $b['documento_id'] - $a['documento_id'];
        })->values();


        return Inertia::render('Inventario/Movimientos', [
            'movimientos' => $movimientos->toArray(),
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'fecha_hasta' => $fecha_hasta ?: '',
            ]
        ]);
    }

    /**
     * Exportar el listado de inventario (tabla de Index) a PDF respetando filtros actuales
     */
    public function exportarInventarioListadoPDF(Request $request)
    {
        try {
            // Replicar exactamente la misma lógica de index() para obtener el inventario filtrado
            $search = $request->input('search', '');
            $fecha_final = $request->input('fecha_final', '');
            $stock_min = $request->input('stock_min', '');
            $stock_max = $request->input('stock_max', '');

            // Obtener todos los productos con sus relaciones
            $query = Producto::with(['categoria', 'marca', 'modelo'])
                ->select('productos.*');

            $productos = $query->get();

            // Validar y normalizar la fecha si existe
            $fechaFiltro = null;
            if ($fecha_final && trim($fecha_final) !== '') {
                try {
                    $fechaFiltro = Carbon::parse($fecha_final)->format('Y-m-d');
                } catch (\Exception $e) {
                    $fechaFiltro = null;
                }
            }

            // Para cada producto, calcular entradas, salidas y stock
            $inventario = $productos->map(function ($producto) use ($fechaFiltro) {
                $driver = DB::connection()->getDriverName();

                // Construir query base para entradas - excluir compras eliminadas (estado = false)
                $entradasQuery = DetalleCompra::join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $producto->id_producto)
                    ->where('compras.estado', false);

                // Aplicar filtro de fecha si existe
                if ($fechaFiltro !== null) {
                    if ($driver === 'sqlite') {
                        $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) <= ?", [$fechaFiltro]);
                    } else {
                        $entradasQuery->whereRaw("DATE(compras.fecha) <= ?", [$fechaFiltro]);
                    }
                }

                $entradas = $entradasQuery->select(
                    DB::raw('SUM(detalle_compras.cantidad) as total_cantidad'),
                    DB::raw('SUM(detalle_compras.cantidad * detalle_compras.precio_unitario) as total_costo')
                )->first();

                // Construir query base para salidas - excluir ventas eliminadas (estado = false)
                $salidasQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $producto->id_producto)
                    ->where('ventas.estado', false);

                // Aplicar filtro de fecha si existe
                if ($fechaFiltro !== null) {
                    if ($driver === 'sqlite') {
                        $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) <= ?", [$fechaFiltro]);
                    } else {
                        $salidasQuery->whereRaw("DATE(ventas.fecha) <= ?", [$fechaFiltro]);
                    }
                }

                $salidas = $salidasQuery->select(
                    DB::raw('SUM(detalle_ventas.cantidad) as total_cantidad'),
                    DB::raw('SUM(detalle_ventas.cantidad * detalle_ventas.precio_unitario) as total_ingresos')
                )->first();

                $total_entradas = floatval($entradas->total_cantidad ?? 0);
                $total_salidas = floatval($salidas->total_cantidad ?? 0);
                $stock_actual = $total_entradas - $total_salidas;

                $costo_total = floatval($entradas->total_costo ?? 0);
                $ingresos_total = floatval($salidas->total_ingresos ?? 0);

                // Costo unitario promedio - evitar división por cero
                $costo_unitario = $total_entradas > 0 ? $costo_total / $total_entradas : 0;

                // Obtener el precio de la última compra realizada del producto (hasta la fecha filtrada si existe)
                $ultimaCompraQuery = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $producto->id_producto)
                    ->where('compras.estado', false);

                if ($fechaFiltro !== null) {
                    if ($driver === 'sqlite') {
                        $ultimaCompraQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) <= ?", [$fechaFiltro]);
                    } else {
                        $ultimaCompraQuery->whereRaw("DATE(compras.fecha) <= ?", [$fechaFiltro]);
                    }
                }

                $ultimaCompra = $ultimaCompraQuery->orderBy('compras.fecha', 'desc')
                    ->orderBy('compras.id_compra', 'desc')
                    ->select('detalle_compras.precio_unitario')
                    ->first();

                $precio_ultima_compra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;

                // Calcular ganancia real sumando las ganancias de cada venta individual
                // Para cada venta, se usa el último precio de compra hasta la fecha de esa venta
                $ganancia_bruta = 0;
                if ($total_salidas > 0) {
                    // Obtener todos los detalles de venta del producto
                    $detallesVentaQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                        ->where('detalle_ventas.id_producto', $producto->id_producto)
                        ->where('ventas.estado', false)
                        ->select('detalle_ventas.*', 'ventas.fecha as fecha_venta');

                    // Aplicar filtro de fecha si existe
                    if ($fechaFiltro !== null) {
                        if ($driver === 'sqlite') {
                            $detallesVentaQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) <= ?", [$fechaFiltro]);
                        } else {
                            $detallesVentaQuery->whereRaw("DATE(ventas.fecha) <= ?", [$fechaFiltro]);
                        }
                    }

                    $detallesVenta = $detallesVentaQuery->get();

                    // Para cada detalle de venta, calcular su ganancia usando el último precio de compra hasta esa fecha
                    foreach ($detallesVenta as $detalle) {
                        $fechaVenta = $detalle->fecha_venta;
                        $precioVenta = floatval($detalle->precio_unitario);
                        $cantidad = floatval($detalle->cantidad);

                        // Buscar el último precio de compra hasta la fecha de esta venta
                        $ultimaCompraQuery = DB::table('detalle_compras')
                            ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                            ->where('detalle_compras.id_producto', $producto->id_producto)
                            ->where('compras.estado', false);

                        if ($driver === 'sqlite') {
                            $ultimaCompraQuery->whereRaw("datetime(compras.fecha) <= datetime(?)", [$fechaVenta]);
                        } else {
                            $ultimaCompraQuery->where('compras.fecha', '<=', $fechaVenta);
                        }

                        $ultimaCompra = $ultimaCompraQuery->orderBy('compras.fecha', 'desc')
                            ->orderBy('compras.id_compra', 'desc')
                            ->select('detalle_compras.precio_unitario')
                            ->first();

                        $precioCompra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;

                        // Si no hay precio de compra, usar el costo unitario promedio como fallback
                        if ($precioCompra == 0 && $costo_unitario > 0) {
                            $precioCompra = $costo_unitario;
                        }

                        // Calcular ganancia de este detalle: (precio_venta - precio_compra) * cantidad
                        $gananciaDetalle = ($precioVenta - $precioCompra) * $cantidad;
                        $ganancia_bruta += $gananciaDetalle;
                    }
                }

                // Calcular ganancia por unidad promedio (solo para referencia)
                $ganancia_por_unidad = $total_salidas > 0 ? ($ganancia_bruta / $total_salidas) : 0;

                // Calcular valor de stock usando el precio de la última compra disponible
                $ultimoPrecio = $precio_ultima_compra > 0 ? $precio_ultima_compra : $costo_unitario;
                $valor_stock = $stock_actual * $ultimoPrecio;

                // Validar que todos los valores sean números válidos
                $costo_total = is_numeric($costo_total) ? $costo_total : 0;
                $ingresos_total = is_numeric($ingresos_total) ? $ingresos_total : 0;
                $ganancia_bruta = is_numeric($ganancia_bruta) ? $ganancia_bruta : 0;
                $costo_unitario = is_numeric($costo_unitario) ? $costo_unitario : 0;
                $valor_stock = is_numeric($valor_stock) ? $valor_stock : 0;

                return [
                    'id_producto' => $producto->id_producto,
                    'producto' => $producto->descripcion,
                    'modelo' => $producto->modelo?->nombre ?? 'N/A',
                    'marca' => $producto->marca?->nombre ?? 'N/A',
                    'categoria' => $producto->categoria?->nombre ?? 'N/A',
                    'total_entradas' => $total_entradas,
                    'costo_total_entradas' => $costo_total,
                    'costo_unitario' => $costo_unitario,
                    'total_salidas' => $total_salidas,
                    'ingresos_totales' => $ingresos_total,
                    'ganancia_bruta' => $ganancia_bruta,
                    'ganancia_por_unidad' => $ganancia_por_unidad,
                    'stock_actual' => $stock_actual,
                    'valor_stock' => $valor_stock,
                    'precio_venta' => $producto->precio_venta,
                    'precio_venta_promedio' => $total_salidas > 0 ? $ingresos_total / $total_salidas : 0,
                ];
            });

            // Si hay filtro de fecha, mostrar solo productos con movimientos hasta esa fecha
            if ($fechaFiltro !== null) {
                $inventario = $inventario->filter(function ($item) {
                    return ($item['total_entradas'] > 0 || $item['total_salidas'] > 0 || $item['stock_actual'] != 0);
                })->values();
            }

            // Aplicar filtro de búsqueda después de mapear
            if ($search && trim($search) !== '') {
                $searchLower = mb_strtolower(trim($search));
                $inventario = $inventario->filter(function ($item) use ($searchLower) {
                    $producto = mb_strtolower($item['producto'] ?? '');
                    $modelo = mb_strtolower($item['modelo'] ?? '');
                    $marca = mb_strtolower($item['marca'] ?? '');
                    $categoria = mb_strtolower($item['categoria'] ?? '');

                    return
                        stripos($producto, $searchLower) !== false ||
                        stripos($modelo, $searchLower) !== false ||
                        stripos($marca, $searchLower) !== false ||
                        stripos($categoria, $searchLower) !== false;
                })->values();
            }

            // Aplicar filtros por rango de stock
            if (($stock_min !== '' && $stock_min !== null && $stock_min !== '0') || ($stock_max !== '' && $stock_max !== null && $stock_max !== '∞')) {
                $inventario = $inventario->filter(function ($item) use ($stock_min, $stock_max) {
                    $stock = $item['stock_actual'];

                    if ($stock_min !== '' && $stock_min !== null && $stock_min !== '0') {
                        if ($stock < (float)$stock_min) {
                            return false;
                        }
                    }

                    if ($stock_max !== '' && $stock_max !== null && $stock_max !== '∞') {
                        if ($stock > (float)$stock_max) {
                            return false;
                        }
                    }

                    return true;
                })->values();
            }

            // Convertir a Array
            $inventarioArray = $inventario->toArray();

            // Calcular totales
            $totalInvertido = 0;
            $totalIngresos = 0;
            $totalGanancia = 0;
            $valorTotalStock = 0;

            foreach ($inventarioArray as $item) {
                $totalInvertido += floatval($item['costo_total_entradas'] ?? 0);
                $totalIngresos += floatval($item['ingresos_totales'] ?? 0);
                $totalGanancia += floatval($item['ganancia_bruta'] ?? 0);
                $valorTotalStock += floatval($item['valor_stock'] ?? 0);
            }

            // Obtener información del usuario
            $usuario = auth()->user();

            // Generar PDF
            $pdf = Pdf::loadView('reportes.inventario-listado-pdf', [
                'inventario' => $inventarioArray,
                'filters' => [
                    'search' => $search,
                    'fecha_final' => $fecha_final,
                    'stock_min' => $stock_min,
                    'stock_max' => $stock_max,
                ],
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                'usuario' => $usuario,
                'totales' => [
                    'total_invertido' => $totalInvertido,
                    'total_ingresos' => $totalIngresos,
                    'ganancia_total' => $totalGanancia,
                    'valor_stock' => $valorTotalStock,
                ]
            ]);

            $pdf->setPaper('A4', 'landscape');

            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'inventario_listado_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error exportarInventarioListadoPDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Exportar movimientos de inventario en PDF
     */
    public function exportarMovimientosPDF(Request $request)
    {
        try {
            // Usar la misma lógica del método movimientos para obtener los datos filtrados
            $search = $request->input('search', '');
            $tipo = $request->input('tipo', '');
            $fecha_hasta = $request->input('fecha_hasta', '');

            // Fecha desde siempre fija: 1 de enero de 2025
            $fechaDesde = '2025-01-01';

            // Validar y normalizar la fecha hasta
            $fechaHasta = null;

            if ($fecha_hasta && trim($fecha_hasta) !== '') {
                try {
                    $fechaHasta = Carbon::parse($fecha_hasta)->format('Y-m-d');
                } catch (\Exception $e) {
                    $fechaHasta = null;
                }
            }

            $movimientos = collect();

            // Obtener entradas (compras) - excluir compras eliminadas
            if ($tipo === '' || $tipo === 'entrada') {
                $entradasQuery = DetalleCompra::join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('compras.estado', false)
                    ->select('detalle_compras.*', 'compras.fecha', 'compras.id_compra as compra_id');

                $driver = DB::connection()->getDriverName();
                if ($fechaHasta !== null) {
                    if ($driver === 'sqlite') {
                        $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) BETWEEN ? AND ?", [$fechaDesde, $fechaHasta]);
                    } else {
                        $entradasQuery->whereBetween(DB::raw('DATE(compras.fecha)'), [$fechaDesde, $fechaHasta]);
                    }
                } else {
                    if ($driver === 'sqlite') {
                        $entradasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) >= ?", [$fechaDesde]);
                    } else {
                        $entradasQuery->whereRaw("DATE(compras.fecha) >= ?", [$fechaDesde]);
                    }
                }

                $entradas = $entradasQuery->get();

                $entradas->load([
                    'producto.modelo',
                    'producto.marca',
                    'producto.categoria'
                ]);

                $compraIds = $entradas->pluck('compra_id')->unique();
                $compras = \App\Models\Compra::with(['usuario', 'proveedor'])
                    ->whereIn('id_compra', $compraIds)
                    ->get()
                    ->keyBy('id_compra');

                $entradas = $entradas->map(function ($detalle) use ($compras) {
                    $compra = $compras->get($detalle->compra_id);
                    return [
                        'fecha' => $detalle->fecha,
                        'tipo' => 'Entrada',
                        'producto' => $detalle->producto->modelo->nombre ?? 'N/A',
                        'marca' => $detalle->producto->marca->nombre ?? 'N/A',
                        'categoria' => $detalle->producto->categoria->nombre ?? 'N/A',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'total' => $detalle->cantidad * $detalle->precio_unitario,
                        'documento' => 'Compra #' . $detalle->compra_id,
                        'proveedor' => $compra->proveedor->nombre ?? 'N/A',
                        'usuario' => $compra->usuario->name ?? 'N/A',
                    ];
                });

                $movimientos = $movimientos->concat($entradas);
            }

            // Obtener salidas (ventas) - excluir ventas eliminadas
            if ($tipo === '' || $tipo === 'salida') {
                $salidasQuery = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('ventas.estado', false)
                    ->select('detalle_ventas.*', 'ventas.fecha', 'ventas.id_venta as venta_id');

                $driver = DB::connection()->getDriverName();
                if ($fechaHasta !== null) {
                    if ($driver === 'sqlite') {
                        $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) BETWEEN ? AND ?", [$fechaDesde, $fechaHasta]);
                    } else {
                        $salidasQuery->whereBetween(DB::raw('DATE(ventas.fecha)'), [$fechaDesde, $fechaHasta]);
                    }
                } else {
                    if ($driver === 'sqlite') {
                        $salidasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) >= ?", [$fechaDesde]);
                    } else {
                        $salidasQuery->whereRaw("DATE(ventas.fecha) >= ?", [$fechaDesde]);
                    }
                }

                $salidas = $salidasQuery->get();

                $salidas->load([
                    'producto.modelo',
                    'producto.marca',
                    'producto.categoria'
                ]);

                $ventaIds = $salidas->pluck('venta_id')->unique();
                $ventas = \App\Models\Venta::with(['usuario', 'cliente'])
                    ->whereIn('id_venta', $ventaIds)
                    ->get()
                    ->keyBy('id_venta');

                $salidas = $salidas->map(function ($detalle) use ($ventas) {
                    $venta = $ventas->get($detalle->venta_id);
                    return [
                        'fecha' => $detalle->fecha,
                        'tipo' => 'Salida',
                        'producto' => $detalle->producto->modelo->nombre ?? 'N/A',
                        'marca' => $detalle->producto->marca->nombre ?? 'N/A',
                        'categoria' => $detalle->producto->categoria->nombre ?? 'N/A',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'total' => $detalle->cantidad * $detalle->precio_unitario,
                        'documento' => 'Venta #' . $detalle->venta_id,
                        'cliente' => $venta->cliente->nombre ?? 'N/A',
                        'usuario' => $venta->usuario->name ?? 'N/A',
                    ];
                });

                $movimientos = $movimientos->concat($salidas);
            }

            // Aplicar filtro de búsqueda por texto si existe
            if ($search && trim($search) !== '') {
                $searchLower = strtolower(trim($search));
                $movimientos = $movimientos->filter(function ($movimiento) use ($searchLower) {
                    return
                        stripos($movimiento['producto'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['marca'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['categoria'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['documento'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['proveedor'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['cliente'] ?? '', $searchLower) !== false ||
                        stripos($movimiento['usuario'] ?? '', $searchLower) !== false;
                })->values();
            }

            // Ordenar por fecha descendente
            $movimientos = $movimientos->sort(function ($a, $b) {
                $fechaA = strtotime($a['fecha']);
                $fechaB = strtotime($b['fecha']);
                if ($fechaB != $fechaA) {
                    return $fechaB - $fechaA;
                }
                return 0;
            })->values();

            // Calcular totales
            $totalEntradas = $movimientos->where('tipo', 'Entrada')->sum('cantidad');
            $totalSalidas = $movimientos->where('tipo', 'Salida')->sum('cantidad');
            $valorEntradas = $movimientos->where('tipo', 'Entrada')->sum('total');
            $valorSalidas = $movimientos->where('tipo', 'Salida')->sum('total');

            // Generar PDF
            $pdf = Pdf::loadView('reportes.movimientos-pdf', [
                'movimientos' => $movimientos->toArray(),
                'filtros' => [
                    'tipo' => $tipo,
                    'fecha_desde' => $fechaDesde,
                    'fecha_hasta' => $fechaHasta,
                    'search' => $search,
                ],
                'totales' => [
                    'entradas' => $totalEntradas,
                    'salidas' => $totalSalidas,
                    'valor_entradas' => $valorEntradas,
                    'valor_salidas' => $valorSalidas,
                ],
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ]);

            $pdf->setPaper('A4', 'landscape');

            // Convertir PDF a base64
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'movimientos_inventario_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en exportarMovimientosPDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra reportes y estadísticas del inventario
     */
    public function reportes()
    {
        // Valor total del inventario
        $productos = Producto::all();
        $valor_total_inventario = 0;
        $total_stock = 0;

        foreach ($productos as $producto) {
            // Filtrar compras eliminadas (estado = false para compras activas)
            $entradas = floatval(DetalleCompra::where('id_producto', $producto->id_producto)
                ->whereHas('compra', function($q) {
                    $q->where('estado', false);
                })
                ->sum(DB::raw('cantidad * precio_unitario')) ?? 0);

            // Filtrar ventas eliminadas (estado = false para ventas activas)
            $salidas_cantidad = floatval(DetalleVenta::where('id_producto', $producto->id_producto)
                ->whereHas('venta', function($q) {
                    $q->where('estado', false);
                })
                ->sum('cantidad') ?? 0);

            $entradas_cantidad = floatval(DetalleCompra::where('id_producto', $producto->id_producto)
                ->whereHas('compra', function($q) {
                    $q->where('estado', false);
                })
                ->sum('cantidad') ?? 0);

            $stock = $entradas_cantidad - $salidas_cantidad;
            $total_stock += $stock;

            if ($stock > 0) {
                // Tomar precio de la última compra disponible
                $ultimaCompra = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $producto->id_producto)
                    ->where('compras.estado', false)
                    ->orderBy('compras.fecha', 'desc')
                    ->orderBy('compras.id_compra', 'desc')
                    ->select('detalle_compras.precio_unitario')
                    ->first();

                $costoUnitario = $ultimaCompra ? (float) $ultimaCompra->precio_unitario : 0.0;
                if ($costoUnitario === 0.0) {
                    // fallback al costo promedio
                    $costoUnitario = $entradas_cantidad > 0 ? ($entradas / $entradas_cantidad) : 0.0;
                }

                $valor_stock_item = $stock * $costoUnitario;
                $valor_total_inventario += is_numeric($valor_stock_item) ? $valor_stock_item : 0;
            }
        }

        // Productos más rentables
        $productosRentables = Producto::with(['modelo', 'marca', 'categoria'])
            ->get()
            ->map(function ($producto) {
                // Filtrar compras eliminadas
                $entradas = DetalleCompra::where('id_producto', $producto->id_producto)
                    ->whereHas('compra', function($q) {
                        $q->where('estado', false);
                    })
                    ->select(DB::raw('SUM(cantidad * precio_unitario) as costo'))
                    ->first();

                // Filtrar ventas eliminadas
                $salidas = DetalleVenta::where('id_producto', $producto->id_producto)
                    ->whereHas('venta', function($q) {
                        $q->where('estado', false);
                    })
                    ->select(DB::raw('SUM(cantidad * precio_unitario) as ingresos'))
                    ->first();

                $ganancia = ($salidas->ingresos ?? 0) - ($entradas->costo ?? 0);

                return [
                    'producto' => $producto->modelo->nombre ?? $producto->descripcion,
                    'marca' => $producto->marca->nombre ?? 'N/A',
                    'ganancia' => $ganancia,
                ];
            })
            ->sortByDesc('ganancia')
            ->take(10)
            ->values();

        // Productos con bajo stock (menos de 5 unidades)
        $productosBajoStock = Producto::with(['modelo', 'marca'])
            ->get()
            ->map(function ($producto) {
                // Filtrar compras eliminadas
                $entradas = DetalleCompra::where('id_producto', $producto->id_producto)
                    ->whereHas('compra', function($q) {
                        $q->where('estado', false);
                    })
                    ->sum('cantidad');

                // Filtrar ventas eliminadas
                $salidas = DetalleVenta::where('id_producto', $producto->id_producto)
                    ->whereHas('venta', function($q) {
                        $q->where('estado', false);
                    })
                    ->sum('cantidad');

                $stock = $entradas - $salidas;

                return [
                    'id' => $producto->id_producto,
                    'producto' => $producto->modelo->nombre ?? $producto->descripcion,
                    'marca' => $producto->marca->nombre ?? 'N/A',
                    'stock' => $stock,
                ];
            })
            ->filter(function ($item) {
                return $item['stock'] < 5 && $item['stock'] >= 0;
            })
            ->sortBy('stock')
            ->values();

        // Productos más vendidos - excluir ventas eliminadas
        $productosMasVendidos = DetalleVenta::with(['producto.modelo', 'producto.marca'])
            ->whereHas('venta', function($q) {
                $q->where('estado', false);
            })
            ->select('id_producto', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('id_producto')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get()
            ->map(function ($detalle) {
                return [
                    'producto' => $detalle->producto->modelo->nombre ?? $detalle->producto->descripcion,
                    'marca' => $detalle->producto->marca->nombre ?? 'N/A',
                    'total_vendido' => $detalle->total_vendido,
                ];
            });

        return Inertia::render('Inventario/Reportes', [
            'valor_total_inventario' => $valor_total_inventario,
            'total_stock' => $total_stock,
            'productos_rentables' => $productosRentables,
            'productos_bajo_stock' => $productosBajoStock,
            'productos_mas_vendidos' => $productosMasVendidos,
        ]);
    }

    /**
     * Calcula el valor del stock usando método FIFO (First In First Out)
     * Las primeras unidades compradas son las primeras en venderse
     *
     * IMPORTANTE: Este método considera CADA compra con su precio individual.
     * Si tienes múltiples compras del mismo producto con precios diferentes,
     * cada una se valora independientemente según su precio de compra real.
     *
     * Ejemplo:
     * - Compra 1: 5 unidades a Bs 3,000 c/u
     * - Compra 2: 4 unidades a Bs 2,800 c/u
     * - Compra 3: 3 unidades a Bs 3,200 c/u
     * Si vendes 6 unidades, se consumen primero las 5 de Compra 1 y 1 de Compra 2.
     * El stock restante se valora con los precios de las compras no vendidas.
     *
     * @param int $id_producto ID del producto
     * @param float $stock_actual Cantidad de stock actual
     * @param string|null $fecha_final Fecha límite para considerar (opcional)
     * @return float Valor total del stock basado en precios reales de cada compra individual
     */
    private function calcularValorStockFIFO($id_producto, $stock_actual, $fecha_final = null)
    {
        if ($stock_actual <= 0) {
            return 0;
        }

        $driver = DB::connection()->getDriverName();

        // Obtener TODAS las compras ordenadas por fecha (más antigua primero) - Método FIFO
        // Cada compra mantiene su precio_unitario original (no se promedia)
        $comprasQuery = DB::table('detalle_compras')
            ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
            ->where('detalle_compras.id_producto', $id_producto)
            ->where('compras.estado', false) // Solo compras activas (no eliminadas)
            ->orderBy('compras.fecha', 'asc') // Más antigua primero (FIFO)
            ->orderBy('compras.id_compra', 'asc'); // Para desempates en caso de misma fecha

        if ($fecha_final !== null) {
            if ($driver === 'sqlite') {
                $comprasQuery->whereRaw("strftime('%Y-%m-%d', compras.fecha) <= ?", [$fecha_final]);
            } else {
                $comprasQuery->whereRaw("DATE(compras.fecha) <= ?", [$fecha_final]);
            }
        }

        $compras = $comprasQuery->select(
            'detalle_compras.cantidad',
            'detalle_compras.precio_unitario', // Precio individual de cada compra
            'compras.fecha',
            'compras.id_compra'
        )->get();

        // Obtener todas las ventas ordenadas por fecha (más antigua primero)
        $ventasQuery = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
            ->where('detalle_ventas.id_producto', $id_producto)
            ->where('ventas.estado', false) // Solo ventas activas (no eliminadas)
            ->orderBy('ventas.fecha', 'asc') // Más antigua primero (FIFO)
            ->orderBy('ventas.id_venta', 'asc'); // Para desempates

        if ($fecha_final !== null) {
            if ($driver === 'sqlite') {
                $ventasQuery->whereRaw("strftime('%Y-%m-%d', ventas.fecha) <= ?", [$fecha_final]);
            } else {
                $ventasQuery->whereRaw("DATE(ventas.fecha) <= ?", [$fecha_final]);
            }
        }

        $ventas = $ventasQuery->select('detalle_ventas.cantidad')
            ->get();

        // Simular movimiento FIFO: las ventas consumen de las compras más antiguas primero
        // Esto significa que si compraste a diferentes precios, se consideran todas
        $stock_restante = []; // Array: ['cantidad' => X, 'precio' => Y] - Cada elemento mantiene su precio original
        $total_vendido = $ventas->sum('cantidad');
        $cantidad_pendiente_vender = $total_vendido;

        // Iterar sobre TODAS las compras (cada una con su precio individual)
        foreach ($compras as $compra) {
            $cantidad_disponible = floatval($compra->cantidad);
            $precio_compra = floatval($compra->precio_unitario); // Precio específico de esta compra

            // Si aún hay unidades pendientes de vender, restar de esta compra
            if ($cantidad_pendiente_vender > 0) {
                $cantidad_restante = max(0, $cantidad_disponible - $cantidad_pendiente_vender);
                $cantidad_pendiente_vender = max(0, $cantidad_pendiente_vender - $cantidad_disponible);
            } else {
                $cantidad_restante = $cantidad_disponible;
            }

            // Si quedan unidades de esta compra, agregar al stock restante
            // Manteniendo el precio original de esta compra específica
            if ($cantidad_restante > 0) {
                $stock_restante[] = [
                    'cantidad' => $cantidad_restante,
                    'precio' => $precio_compra // Precio real de esta compra, no un promedio
                ];
            }
        }

        // Calcular el valor total del stock sumando las unidades restantes
        // con sus precios reales (cada compra mantiene su precio original)
        $valor_total = 0;
        foreach ($stock_restante as $item) {
            $valor_total += $item['cantidad'] * $item['precio']; // Cada unidad se valora con su precio de compra original
        }

        return floatval($valor_total);
    }
}

