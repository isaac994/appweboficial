<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Mostrar la página principal de reportes
     */
    public function index()
    {
        return Inertia::render('Reportes/Index');
    }

    /**
     * Mostrar la página de reporte general
     */
    public function general()
    {
        return Inertia::render('Reportes/General');
    }

    /**
     * Generar reporte general con datos
     */
    public function generarReporteGeneral(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            // Validar fechas
            if (!$fechaInicio || !$fechaFin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas de inicio y fin son requeridas'
                ], 400);
            }

            // Obtener datos de compras
            $compras = Compra::with(['detalles.producto'])
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get();

            $totalCompras = $compras->sum(function($compra) {
                return $compra->detalles->sum('total_parcial');
            });

            // Obtener datos de ventas
            $ventas = Venta::with(['detalles.producto'])
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get();

            $totalVentas = $ventas->sum(function($venta) {
                return $venta->detalles->sum('total_parcial');
            });

            // Calcular ganancias
            $ganancias = $totalVentas - $totalCompras;
            $margenGanancia = $totalVentas > 0 ? ($ganancias / $totalVentas) * 100 : 0;

            // Productos más vendidos
            $productosMasVendidos = DB::table('detalle_ventas')
                ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                ->join('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
                ->join('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
                ->leftJoin('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
                ->leftJoin('marcas', 'modelos.id_marca', '=', 'marcas.id_marca')
                ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->select(
                    'productos.id_producto',
                    'productos.descripcion as nombre',
                    'categorias.nombre as categoria',
                    'marcas.nombre as marca',
                    DB::raw('SUM(detalle_ventas.cantidad) as cantidad_vendida'),
                    DB::raw('SUM(detalle_ventas.total_parcial) as ingresos')
                )
                ->groupBy('productos.id_producto', 'productos.descripcion', 'categorias.nombre', 'marcas.nombre')
                ->orderByDesc('cantidad_vendida')
                ->limit(10)
                ->get();

            // Calcular ganancia por producto
            foreach ($productosMasVendidos as $producto) {
                $costoTotal = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $producto->id_producto)
                    ->whereBetween('compras.fecha', [$fechaInicio, $fechaFin])
                    ->sum(DB::raw('detalle_compras.cantidad * detalle_compras.precio_unitario'));

                $producto->ganancia = $producto->ingresos - $costoTotal;
            }

            // Productos con stock bajo (entre 1 y 4 unidades, no 0)
            $productosStockBajo = DB::table('productos')
                ->join('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
                ->leftJoin('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
                ->leftJoin('marcas', 'modelos.id_marca', '=', 'marcas.id_marca')
                ->leftJoin('detalle_compras', 'productos.id_producto', '=', 'detalle_compras.id_producto')
                ->leftJoin('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->leftJoin('detalle_ventas', 'productos.id_producto', '=', 'detalle_ventas.id_producto')
                ->leftJoin('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                ->whereBetween('compras.fecha', [$fechaInicio, $fechaFin])
                ->orWhereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->select(
                    'productos.id_producto',
                    'productos.descripcion as nombre',
                    'categorias.nombre as categoria',
                    'marcas.nombre as marca',
                    DB::raw('COALESCE(SUM(detalle_compras.cantidad), 0) as total_entradas'),
                    DB::raw('COALESCE(SUM(detalle_ventas.cantidad), 0) as total_salidas')
                )
                ->groupBy('productos.id_producto', 'productos.descripcion', 'categorias.nombre', 'marcas.nombre')
                ->havingRaw('(COALESCE(SUM(detalle_compras.cantidad), 0) - COALESCE(SUM(detalle_ventas.cantidad), 0)) BETWEEN 1 AND 4')
                ->get();

            // Análisis por período (por mes) con cálculos detallados
            $analisisPeriodo = [];
            $fechaActual = new \DateTime($fechaInicio);
            $fechaFinal = new \DateTime($fechaFin);
            $totalComprasAnterior = 0;
            $totalVentasAnterior = 0;

            while ($fechaActual <= $fechaFinal) {
                $mesInicio = $fechaActual->format('Y-m-01');
                $mesFin = $fechaActual->format('Y-m-t');

                $comprasMes = Compra::whereBetween('fecha', [$mesInicio, $mesFin])->get();
                $ventasMes = Venta::whereBetween('fecha', [$mesInicio, $mesFin])->get();

                $totalComprasMes = $comprasMes->sum(function($compra) {
                    return $compra->detalles->sum('total_parcial');
                });

                $totalVentasMes = $ventasMes->sum(function($venta) {
                    return $venta->detalles->sum('total_parcial');
                });

                // Calcular variaciones porcentuales
                $variacionCompras = $totalComprasAnterior > 0 ?
                    (($totalComprasMes - $totalComprasAnterior) / $totalComprasAnterior) * 100 : 0;
                $variacionVentas = $totalVentasAnterior > 0 ?
                    (($totalVentasMes - $totalVentasAnterior) / $totalVentasAnterior) * 100 : 0;

                // Calcular promedio por transacción
                $promedioCompra = $comprasMes->count() > 0 ? $totalComprasMes / $comprasMes->count() : 0;
                $promedioVenta = $ventasMes->count() > 0 ? $totalVentasMes / $ventasMes->count() : 0;

                $analisisPeriodo[] = [
                    'periodo' => $fechaActual->format('M Y'),
                    'compras' => $totalComprasMes,
                    'ventas' => $totalVentasMes,
                    'ganancias' => $totalVentasMes - $totalComprasMes,
                    'transacciones' => $comprasMes->count() + $ventasMes->count(),
                    'variacion_compras' => round($variacionCompras, 2),
                    'variacion_ventas' => round($variacionVentas, 2),
                    'promedio_compra' => round($promedioCompra, 2),
                    'promedio_venta' => round($promedioVenta, 2),
                    'margen_mes' => $totalVentasMes > 0 ? round((($totalVentasMes - $totalComprasMes) / $totalVentasMes) * 100, 2) : 0
                ];

                $totalComprasAnterior = $totalComprasMes;
                $totalVentasAnterior = $totalVentasMes;
                $fechaActual->modify('+1 month');
            }

            // Análisis de tendencias y métricas adicionales
            $tendencias = $this->calcularTendencias($analisisPeriodo);
            $metricasRendimiento = $this->calcularMetricasRendimiento($compras, $ventas, $fechaInicio, $fechaFin);

            // Calcular stock actual para productos con stock bajo
            foreach ($productosStockBajo as $producto) {
                $producto->stock_actual = $producto->total_entradas - $producto->total_salidas;
            }

            $datos = [
                'totales' => [
                    'total_compras' => $totalCompras,
                    'total_ventas' => $totalVentas,
                    'ganancias' => $ganancias,
                    'margen_ganancia' => round($margenGanancia, 2),
                    'numero_compras' => $compras->count(),
                    'numero_ventas' => $ventas->count(),
                    'total_productos_vendidos' => $ventas->sum(function($venta) {
                        return $venta->detalles->sum('cantidad');
                    }),
                    'promedio_compra' => $compras->count() > 0 ? round($totalCompras / $compras->count(), 2) : 0,
                    'promedio_venta' => $ventas->count() > 0 ? round($totalVentas / $ventas->count(), 2) : 0,
                    'rotacion_inventario' => $metricasRendimiento['rotacion_inventario'],
                    'dias_inventario' => $metricasRendimiento['dias_inventario']
                ],
                'productos_mas_vendidos' => $productosMasVendidos,
                'productos_stock_bajo' => $productosStockBajo,
                'analisis_periodo' => $analisisPeriodo,
                'tendencias' => $tendencias,
                'metricas_rendimiento' => $metricasRendimiento
            ];

            return response()->json([
                'success' => true,
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteGeneral: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte general en PDF
     */
    public function exportarReporteGeneralPDF(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            // Obtener los mismos datos que en generarReporteGeneral
            $response = $this->generarReporteGeneral($request);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return response()->json($data, 400);
            }

            $datos = $data['data'];

            // Generar PDF
            $pdf = Pdf::loadView('reportes.general', [
                'datos' => $datos,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ]);

            $pdf->setPaper('A4', 'portrait');

            // Convertir PDF a base64
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_general_' . $fechaInicio . '_' . $fechaFin . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en exportarReporteGeneralPDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la página de reporte de inventario
     */
    public function inventario()
    {
        return Inertia::render('Reportes/Inventario');
    }

    /**
     * Exportar inventario en PDF
     */
    public function exportarInventarioPDF(Request $request)
    {
        try {
            // Obtener datos del inventario usando el controlador existente
            $inventarioController = new \App\Http\Controllers\InventarioController();
            $inventarioData = $inventarioController->index($request);

            // Generar PDF
            $pdf = Pdf::loadView('reportes.inventario', [
                'inventario' => $inventarioData->getData()['page']['props']['inventario'],
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ]);

            $pdf->setPaper('A4', 'landscape');

            // Convertir PDF a base64
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_inventario_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en exportarInventarioPDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF de reportes de inventario (página de reportes con estadísticas)
     */
    public function generarPDFReportesInventario(Request $request)
    {
        try {
            \Log::info('Iniciando generación de PDF de reportes de inventario');

            // Obtener los datos usando el método reportes del InventarioController
            $inventarioController = new \App\Http\Controllers\InventarioController();

            // Replicar la lógica del método reportes()
            $productos = Producto::all();
            $valor_total_inventario = 0;
            $total_stock = 0;

            foreach ($productos as $producto) {
                // Filtrar compras eliminadas (estado = false para compras activas)
                $entradas_cantidad = floatval(\DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $producto->id_producto)
                    ->where('compras.estado', false)
                    ->sum('detalle_compras.cantidad') ?? 0);

                // Filtrar ventas eliminadas (estado = false para ventas activas)
                $salidas_cantidad = floatval(\DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $producto->id_producto)
                    ->where('ventas.estado', false)
                    ->sum('detalle_ventas.cantidad') ?? 0);

                $stock = $entradas_cantidad - $salidas_cantidad;
                $total_stock += $stock;

                if ($stock > 0) {
                    // Calcular valor de stock usando método FIFO
                    $valor_stock_item = $this->calcularValorStockFIFO($producto->id_producto, $stock, null);
                    $valor_total_inventario += is_numeric($valor_stock_item) ? $valor_stock_item : 0;
                }
            }

            // Productos más rentables
            $productosRentables = Producto::with(['modelo', 'marca', 'categoria'])
                ->get()
                ->map(function ($producto) {
                    // Filtrar compras eliminadas
                    $entradas = \DB::table('detalle_compras')
                        ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                        ->where('detalle_compras.id_producto', $producto->id_producto)
                        ->where('compras.estado', false)
                        ->select(\DB::raw('SUM(cantidad * precio_unitario) as costo'))
                        ->first();

                    // Filtrar ventas eliminadas
                    $salidas = \DB::table('detalle_ventas')
                        ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                        ->where('detalle_ventas.id_producto', $producto->id_producto)
                        ->where('ventas.estado', false)
                        ->select(\DB::raw('SUM(cantidad * precio_unitario) as ingresos'))
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
                    $entradas = \DB::table('detalle_compras')
                        ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                        ->where('detalle_compras.id_producto', $producto->id_producto)
                        ->where('compras.estado', false)
                        ->sum('detalle_compras.cantidad');

                    // Filtrar ventas eliminadas
                    $salidas = \DB::table('detalle_ventas')
                        ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                        ->where('detalle_ventas.id_producto', $producto->id_producto)
                        ->where('ventas.estado', false)
                        ->sum('detalle_ventas.cantidad');

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
            $productosMasVendidos = \DB::table('detalle_ventas')
                ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                ->join('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
                ->leftJoin('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
                ->leftJoin('marcas', 'modelos.id_marca', '=', 'marcas.id_marca')
                ->where('ventas.estado', false)
                ->select(
                    'productos.id_producto',
                    'modelos.nombre as producto_nombre',
                    'productos.descripcion as producto_descripcion',
                    'marcas.nombre as marca_nombre',
                    \DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
                )
                ->groupBy('productos.id_producto', 'modelos.nombre', 'productos.descripcion', 'marcas.nombre')
                ->orderByDesc('total_vendido')
                ->take(10)
                ->get()
                ->map(function ($detalle) {
                    return [
                        'producto' => $detalle->producto_nombre ?? $detalle->producto_descripcion ?? 'Sin nombre',
                        'marca' => $detalle->marca_nombre ?? 'N/A',
                        'total_vendido' => $detalle->total_vendido,
                    ];
                });

            // Generar HTML para el PDF
            $html = view('reportes.inventario-reportes-pdf', [
                'valor_total_inventario' => $valor_total_inventario,
                'total_stock' => $total_stock,
                'productos_rentables' => $productosRentables,
                'productos_bajo_stock' => $productosBajoStock,
                'productos_mas_vendidos' => $productosMasVendidos,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ])->render();

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Convertir a base64
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_inventario_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF de reportes de inventario: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula el valor del stock usando método FIFO (copiado de InventarioController)
     */
    private function calcularValorStockFIFO($id_producto, $stock_actual, $fecha_final = null)
    {
        if ($stock_actual <= 0) {
            return 0;
        }

        // Obtener TODAS las compras ordenadas por fecha (más antigua primero) - Método FIFO
        $comprasQuery = DB::table('detalle_compras')
            ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
            ->where('detalle_compras.id_producto', $id_producto)
            ->where('compras.estado', false)
            ->orderBy('compras.fecha', 'asc')
            ->orderBy('compras.id_compra', 'asc');

        if ($fecha_final && trim($fecha_final) !== '') {
            $comprasQuery->where('compras.fecha', '<=', $fecha_final);
        }

        $compras = $comprasQuery->select(
            'detalle_compras.cantidad',
            'detalle_compras.precio_unitario',
            'compras.fecha',
            'compras.id_compra'
        )->get();

        // Obtener todas las ventas ordenadas por fecha (más antigua primero)
        $ventasQuery = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
            ->where('detalle_ventas.id_producto', $id_producto)
            ->where('ventas.estado', false)
            ->orderBy('ventas.fecha', 'asc')
            ->orderBy('ventas.id_venta', 'asc');

        if ($fecha_final && trim($fecha_final) !== '') {
            $ventasQuery->where('ventas.fecha', '<=', $fecha_final);
        }

        $ventas = $ventasQuery->select('detalle_ventas.cantidad')
            ->get();

        // Simular movimiento FIFO
        $stock_restante = [];
        $total_vendido = $ventas->sum('cantidad');
        $cantidad_pendiente_vender = $total_vendido;

        foreach ($compras as $compra) {
            $cantidad_disponible = floatval($compra->cantidad);
            $precio_compra = floatval($compra->precio_unitario);

            if ($cantidad_pendiente_vender > 0) {
                $cantidad_restante = max(0, $cantidad_disponible - $cantidad_pendiente_vender);
                $cantidad_pendiente_vender = max(0, $cantidad_pendiente_vender - $cantidad_disponible);
            } else {
                $cantidad_restante = $cantidad_disponible;
            }

            if ($cantidad_restante > 0) {
                $stock_restante[] = [
                    'cantidad' => $cantidad_restante,
                    'precio' => $precio_compra
                ];
            }
        }

        // Calcular el valor total del stock
        $valor_total = 0;
        foreach ($stock_restante as $item) {
            $valor_total += $item['cantidad'] * $item['precio'];
        }

        return floatval($valor_total);
    }

    /**
     * Calcular tendencias de crecimiento
     */
    private function calcularTendencias($analisisPeriodo)
    {
        if (count($analisisPeriodo) < 2) {
            return [
                'tendencia_compras' => 'Sin datos suficientes',
                'tendencia_ventas' => 'Sin datos suficientes',
                'tendencia_ganancias' => 'Sin datos suficientes',
                'crecimiento_promedio_compras' => 0,
                'crecimiento_promedio_ventas' => 0,
                'crecimiento_promedio_ganancias' => 0
            ];
        }

        $compras = array_column($analisisPeriodo, 'compras');
        $ventas = array_column($analisisPeriodo, 'ventas');
        $ganancias = array_column($analisisPeriodo, 'ganancias');

        // Calcular crecimiento promedio
        $crecimientoCompras = $this->calcularCrecimientoPromedio($compras);
        $crecimientoVentas = $this->calcularCrecimientoPromedio($ventas);
        $crecimientoGanancias = $this->calcularCrecimientoPromedio($ganancias);

        return [
            'tendencia_compras' => $crecimientoCompras >= 5 ? 'Crecimiento' : ($crecimientoCompras <= -5 ? 'Decrecimiento' : 'Estable'),
            'tendencia_ventas' => $crecimientoVentas >= 5 ? 'Crecimiento' : ($crecimientoVentas <= -5 ? 'Decrecimiento' : 'Estable'),
            'tendencia_ganancias' => $crecimientoGanancias >= 5 ? 'Crecimiento' : ($crecimientoGanancias <= -5 ? 'Decrecimiento' : 'Estable'),
            'crecimiento_promedio_compras' => round($crecimientoCompras, 2),
            'crecimiento_promedio_ventas' => round($crecimientoVentas, 2),
            'crecimiento_promedio_ganancias' => round($crecimientoGanancias, 2)
        ];
    }

    /**
     * Calcular crecimiento promedio de una serie de datos
     */
    private function calcularCrecimientoPromedio($datos)
    {
        if (count($datos) < 2) return 0;

        $crecimientos = [];
        for ($i = 1; $i < count($datos); $i++) {
            if ($datos[$i-1] > 0) {
                $crecimientos[] = (($datos[$i] - $datos[$i-1]) / $datos[$i-1]) * 100;
            }
        }

        return count($crecimientos) > 0 ? array_sum($crecimientos) / count($crecimientos) : 0;
    }

    /**
     * Calcular métricas de rendimiento del negocio
     */
    private function calcularMetricasRendimiento($compras, $ventas, $fechaInicio, $fechaFin)
    {
        // Calcular días del período
        $fechaIni = new \DateTime($fechaInicio);
        $fechaFin = new \DateTime($fechaFin);
        $diasPeriodo = $fechaIni->diff($fechaFin)->days + 1;

        // Calcular total de productos únicos vendidos
        $productosUnicosVendidos = $ventas->flatMap(function($venta) {
            return $venta->detalles->pluck('id_producto');
        })->unique()->count();

        // Calcular total de productos únicos comprados
        $productosUnicosComprados = $compras->flatMap(function($compra) {
            return $compra->detalles->pluck('id_producto');
        })->unique()->count();

        // Calcular rotación de inventario (aproximada)
        $totalInventarioVendido = $ventas->sum(function($venta) {
            return $venta->detalles->sum('cantidad');
        });

        $promedioInventario = $totalInventarioVendido / 2; // Aproximación simple
        $rotacionInventario = $promedioInventario > 0 ? $totalInventarioVendido / $promedioInventario : 0;

        // Calcular días de inventario
        $diasInventario = $rotacionInventario > 0 ? 365 / $rotacionInventario : 0;

        // Calcular eficiencia de ventas (ventas por día)
        $ventasPorDia = $diasPeriodo > 0 ? $ventas->sum(function($venta) {
            return $venta->detalles->sum('total_parcial');
        }) / $diasPeriodo : 0;

        // Calcular eficiencia de compras (compras por día)
        $comprasPorDia = $diasPeriodo > 0 ? $compras->sum(function($compra) {
            return $compra->detalles->sum('total_parcial');
        }) / $diasPeriodo : 0;

        return [
            'rotacion_inventario' => round($rotacionInventario, 2),
            'dias_inventario' => round($diasInventario, 2),
            'productos_unicos_vendidos' => $productosUnicosVendidos,
            'productos_unicos_comprados' => $productosUnicosComprados,
            'ventas_por_dia' => round($ventasPorDia, 2),
            'compras_por_dia' => round($comprasPorDia, 2),
            'dias_periodo' => $diasPeriodo,
            'promedio_transacciones_dia' => $diasPeriodo > 0 ? round(($compras->count() + $ventas->count()) / $diasPeriodo, 2) : 0
        ];
    }

    /**
     * Mostrar la página de reportes de productos
     */
    public function productos(Request $request)
    {
        // Obtener filtros de la request
        $filtros = $request->only([
            'search',
            'categoria',
            'marca',
            'estado',
            'fecha_desde',
            'fecha_hasta',
            'ordenar_por',
            'orden'
        ]);

        // Construir la consulta base
        $query = Producto::with(['categoria', 'marca', 'modelo']);

        // Filtro de búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Filtro por marca
        if ($request->filled('marca')) {
            $query->where('id_marca', $request->marca);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'disponible') {
                $query->whereRaw('
                    (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) >
                    (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
                ');
            } elseif ($request->estado === 'agotado') {
                $query->whereRaw('
                    (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) <=
                    (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
                ');
            }
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'modelo.nombre');
        $orden = $request->get('orden', 'asc');

        if ($ordenarPor === 'stock_disponible') {
            // Ordenar por stock disponible (calculado dinámicamente, excluyendo compras eliminadas)
            $query->orderByRaw('
                (SELECT COALESCE(SUM(dc.cantidad), 0)
                 FROM detalle_compras dc
                 JOIN compras c ON dc.id_compra = c.id_compra
                 WHERE dc.id_producto = productos.id_producto AND c.estado = false) -
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
            ' . $orden);
        } elseif ($ordenarPor === 'modelo.nombre') {
            // Ordenar por nombre del modelo
            $query->join('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
                  ->orderBy('modelos.nombre', $orden);
        } else {
            $query->orderBy($ordenarPor, $orden);
        }

        // Obtener productos paginados
        $productos = $query->paginate(20);

        // Calcular stock disponible para cada producto (excluyendo compras eliminadas)
        $productos->getCollection()->transform(function ($producto) {
            $stockComprado = DB::table('detalle_compras')
                ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->where('detalle_compras.id_producto', $producto->id_producto)
                ->where('compras.estado', false) // Solo compras activas
                ->sum('detalle_compras.cantidad');

            $stockVendido = DB::table('detalle_ventas')
                ->where('id_producto', $producto->id_producto)
                ->sum('cantidad');

            $producto->stock_disponible = $stockComprado - $stockVendido;
            $producto->estado_stock = $producto->stock_disponible > 0 ? 'disponible' : 'agotado';

            return $producto;
        });

        // Obtener datos para filtros
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();

        return Inertia::render('Reportes/Productos', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filtros' => $filtros
        ]);
    }

            /**
     * Generar reporte de productos más comprados (PDF)
     */
    public function generarReporteProductos(Request $request)
    {
        try {
            // Obtener filtros del request
            $filtros = $request->only([
                'fecha_desde', 'fecha_hasta', 'limite', 'orden_por'
            ]);

            // Valores por defecto
            $fechaDesde = $filtros['fecha_desde'] ?? null;
            $fechaHasta = $filtros['fecha_hasta'] ?? null;
            $limite = $filtros['limite'] ?? 1;
            $ordenPor = $filtros['orden_por'] ?? 'cantidad';

            // Construir query base
            $query = \DB::table('detalle_compras as dc')
                ->join('compras as c', 'dc.id_compra', '=', 'c.id_compra')
                ->join('productos as p', 'dc.id_producto', '=', 'p.id_producto')
                ->join('marcas as m', 'p.id_marca', '=', 'm.id_marca')
                ->join('modelos as mod', 'p.id_modelo', '=', 'mod.id_modelo')
                ->join('categorias as cat', 'p.id_categoria', '=', 'cat.id_categoria')
                ->select(
                    'p.id_producto',
                    'p.nombre as producto_nombre',
                    'p.descripcion',
                    'm.nombre as marca_nombre',
                    'mod.nombre as modelo_nombre',
                    'cat.nombre as categoria_nombre',
                    \DB::raw('SUM(dc.cantidad) as total_cantidad'),
                    \DB::raw('SUM(dc.total_parcial) as total_monto'),
                    \DB::raw('AVG(dc.precio_unitario) as precio_promedio')
                )
                ->groupBy('p.id_producto', 'p.nombre', 'p.descripcion', 'm.nombre', 'mod.nombre', 'cat.nombre');

            // Aplicar filtros de fecha
            if ($fechaDesde) {
                $query->where('c.fecha', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('c.fecha', '<=', $fechaHasta);
            }

            // Aplicar ordenamiento
            if ($ordenPor === 'cantidad') {
                $query->orderBy('total_cantidad', 'desc');
            } else {
                $query->orderBy('total_monto', 'desc');
            }

            // Aplicar límite
            $productos = $query->limit($limite)->get();

            // Calcular estadísticas
            $totalProductos = $productos->count();
            $totalCantidad = $productos->sum('total_cantidad');
            $totalMonto = $productos->sum('total_monto');
            $precioPromedio = $totalMonto > 0 ? $totalMonto / $totalCantidad : 0;

            // Generar PDF
            $pdf = \PDF::loadView('reportes.productos', [
                'productos' => $productos,
                'filtros' => $filtros,
                'estadisticas' => [
                    'total_productos' => $totalProductos,
                    'total_cantidad' => $totalCantidad,
                    'total_monto' => $totalMonto,
                    'precio_promedio' => $precioPromedio,
                ]
            ]);

            $pdfContent = $pdf->output();
            $base64 = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64,
                'filename' => 'reporte_productos_mas_comprados_' . date('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar reporte de productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para verificar la conexión
     */
    public function testReporte()
    {
        try {
            \Log::info('Iniciando prueba de reporte...');

            // Prueba simple de consulta
            $compras = Compra::with(['proveedor', 'detalles.producto.marca'])
                ->limit(5)
                ->get();

            \Log::info('Compras encontradas en prueba: ' . $compras->count());

            // Prueba simple de PDF
            $html = '<html><body><h1>Prueba de Reporte</h1><p>Fecha: ' . now() . '</p></body></html>';
            $pdf = Pdf::loadHTML($html);
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            \Log::info('PDF de prueba generado exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Prueba exitosa',
                'compras_count' => $compras->count(),
                'pdf_size' => strlen($base64Pdf)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en prueba: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para verificar que DomPDF funciona
     */
    public function testPdf()
    {
        try {
            // Crear un PDF simple de prueba
            $html = '<h1>Test PDF</h1><p>Este es un PDF de prueba.</p>';
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->download('test.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Método de prueba simple para reporte de ventas
     */
    public function testVentas()
    {
        try {
            // Paso 1: Verificar si hay ventas
            $ventasCount = Venta::where('estado', false)->count();

            if ($ventasCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ventas activas en la base de datos',
                    'ventas_count' => $ventasCount
                ], 400);
            }

            // Paso 2: Obtener ventas con relaciones básicas
            $ventas = Venta::with(['cliente', 'detalles.producto'])
                ->where('estado', false)
                ->limit(3)
                ->get();

            // Paso 3: Verificar que las relaciones estén cargadas
            foreach ($ventas as $venta) {
                if (!$venta->cliente) {
                    throw new \Exception("Cliente no encontrado para venta ID: {$venta->id_venta}");
                }
                foreach ($venta->detalles as $detalle) {
                    if (!$detalle->producto) {
                        throw new \Exception("Producto no encontrado en detalle ID: {$detalle->id_detalle_venta}");
                    }
                }
            }

            // Paso 4: Generar HTML simple
            $html = view('reportes.ventas', [
                'ventas' => $ventas,
                'totalVentas' => 1000,
                'totalGanancia' => 200,
                'cantidadVentas' => $ventas->count(),
                'fechaInicio' => '2025-01-01',
                'fechaFin' => '2025-12-31',
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                'filtros' => [
                    'fecha_desde' => '2025-01-01',
                    'fecha_hasta' => '2025-12-31',
                    'cliente' => null,
                    'monto_minimo' => null,
                    'monto_maximo' => null,
                    'categorias' => [],
                    'ordenar_por' => 'fecha'
                ]
            ])->render();

            // Paso 5: Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'test_ventas_' . now()->format('Y-m-d_H-i-s') . '.pdf',
                'ventas_count' => $ventas->count(),
                'html_length' => strlen($html)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en testVentas: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte de prueba: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Método que simula generarReporteVentas con datos mínimos
     */
    public function testGenerarVentas()
    {
        try {
            // Simular los mismos pasos que generarReporteVentas pero con datos mínimos
            $fechaInicio = '2025-01-01';
            $fechaFin = '2025-12-31';

            // Construir query igual que el original
            $query = Venta::with(['cliente', 'detalles.producto', 'detalles.producto.categoria', 'detalles.producto.marca', 'detalles.producto.modelo', 'usuario'])
                ->where('estado', false)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->limit(2); // Solo 2 ventas para prueba

            $ventas = $query->get();

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ventas en el rango de fechas especificado',
                    'query_sql' => $query->toSql(),
                    'query_bindings' => $query->getBindings()
                ], 400);
            }

            // Calcular totales como en el original
            $totalVentas = $ventas->sum(function($venta) {
                return $venta->detalles->sum('total_parcial');
            });

            $totalGanancia = 0; // Simplificado para prueba
            $cantidadVentas = $ventas->count();

            // Generar HTML igual que el original
            $html = view('reportes.ventas', [
                'ventas' => $ventas,
                'totalVentas' => $totalVentas,
                'totalGanancia' => $totalGanancia,
                'cantidadVentas' => $cantidadVentas,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                'filtros' => [
                    'fecha_desde' => $fechaInicio,
                    'fecha_hasta' => $fechaFin,
                    'cliente' => null,
                    'monto_minimo' => null,
                    'monto_maximo' => null,
                    'categorias' => [],
                    'ordenar_por' => 'fecha'
                ]
            ])->render();

            // Generar PDF igual que el original
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'test_generar_ventas_' . now()->format('Y-m-d_H-i-s') . '.pdf',
                'ventas_count' => $ventas->count(),
                'html_length' => strlen($html)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en testGenerarVentas: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte de prueba: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function testSimple()
    {
        try {
            // Solo verificar que DomPDF funciona
            $html = '<h1>Test Simple</h1><p>Este es un test básico.</p>';
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'message' => 'PDF generado correctamente',
                'pdf_size' => strlen($base64Pdf)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function verificarDatos()
    {
        try {
            $ventasActivas = Venta::where('estado', false)->count();
            $totalVentas = Venta::count();
            $clientes = Cliente::count();
            $productos = Producto::count();

            return response()->json([
                'success' => true,
                'data' => [
                    'ventas_activas' => $ventasActivas,
                    'total_ventas' => $totalVentas,
                    'clientes' => $clientes,
                    'productos' => $productos
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function diagnosticar()
    {
        try {
            $compras = Compra::with(['detalles.producto.marca', 'detalles.producto.modelo', 'detalles.producto.categoria'])->get();

            $resultado = [
                'total_compras' => $compras->count(),
                'compras_con_detalles' => $compras->filter(function($c) { return $c->detalles->isNotEmpty(); })->count(),
                'detalles_con_producto' => 0,
                'productos_con_marca' => 0,
                'productos_con_modelo' => 0,
                'errores' => []
            ];

            foreach ($compras as $compra) {
                foreach ($compra->detalles as $detalle) {
                    if ($detalle->producto) {
                        $resultado['detalles_con_producto']++;

                        if ($detalle->producto->marca) {
                            $resultado['productos_con_marca']++;
                        } else {
                            $resultado['errores'][] = "Producto {$detalle->producto->id_producto} sin marca";
                        }

                        if ($detalle->producto->modelo) {
                            $resultado['productos_con_modelo']++;
                        } else {
                            $resultado['errores'][] = "Producto {$detalle->producto->id_producto} sin modelo";
                        }
                    } else {
                        $resultado['errores'][] = "Detalle {$detalle->id_detalle_compra} sin producto";
                    }
                }
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Mostrar la página de reportes de proveedores
     */
    public function proveedores(Request $request)
    {
        // Obtener filtros de la request
        $filtros = $request->only([
            'search',
            'ordenar_por',
            'orden'
        ]);

        // Construir la consulta base
        $query = Proveedor::query();

        // Filtro de búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'nombre');
        $orden = $request->get('orden', 'asc');
        $query->orderBy($ordenarPor, $orden);

        // Obtener proveedores paginados
        $proveedores = $query->paginate(20);

        // Calcular estadísticas para cada proveedor
        $proveedores->getCollection()->transform(function ($proveedor) {
            // Obtener compras del proveedor
            $compras = DB::table('compras')
                ->where('id_proveedor', $proveedor->id_proveedor)
                ->get();

            // Calcular total de compras
            $proveedor->total_compras = $compras->count();

            // Calcular monto total desde los detalles de compra (cantidad * precio_unitario)
            $montoTotal = DB::table('detalle_compras')
                ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->where('compras.id_proveedor', $proveedor->id_proveedor)
                ->selectRaw('SUM(cantidad * precio_unitario) as total')
                ->value('total');

            $proveedor->monto_total = $montoTotal;

            return $proveedor;
        });

        return Inertia::render('Reportes/Proveedores', [
            'proveedores' => $proveedores,
            'filtros' => $filtros
        ]);
    }

    /**
     * Generar reporte de proveedores (PDF)
     */
    public function generarReporteProveedores(Request $request)
    {
        try {
            // Obtener filtros del request
            $filtros = $request->only(['search', 'ordenar_por', 'orden']);

            // Construir la consulta base
            $query = Proveedor::query();

            // Filtro de búsqueda por nombre
            if (!empty($filtros['search'])) {
                $query->where('nombre', 'like', '%' . $filtros['search'] . '%');
            }

            // Ordenamiento
            $ordenarPor = $filtros['ordenar_por'] ?? 'nombre';
            $orden = $filtros['orden'] ?? 'asc';
            $query->orderBy($ordenarPor, $orden);

            // Obtener proveedores con filtros aplicados
            $proveedores = $query->get();

            // Calcular estadísticas para cada proveedor
            $proveedores->transform(function ($proveedor) {
                // Obtener compras del proveedor
                $compras = DB::table('compras')
                    ->where('id_proveedor', $proveedor->id_proveedor)
                    ->get();

                // Calcular total de compras
                $proveedor->total_compras = $compras->count();

                // Calcular monto total desde los detalles de compra (cantidad * precio_unitario)
                $montoTotal = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('compras.id_proveedor', $proveedor->id_proveedor)
                    ->selectRaw('SUM(cantidad * precio_unitario) as total')
                    ->value('total');

                $proveedor->monto_total = $montoTotal;

                return $proveedor;
            });

            // Generar PDF
            $html = view('reportes.proveedores', [
                'proveedores' => $proveedores,
                'fecha_generacion' => now()->format('d/m/Y H:i:s')
            ])->render();

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Convertir PDF a base64 para enviar como JSON
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_proveedores_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la página de reportes de compras
     */
    public function compras(Request $request)
    {
        \Log::info('Método compras llamado con parámetros:', $request->all());

        $fechaInicio = $request->input('fecha_inicio', '2020-01-01');
        $fechaFinal = $request->input('fecha_final', date('Y-m-d'));
        $proveedorId = $request->input('proveedor', 'todos');

        // Obtener todas las categorías para marcarlas por defecto
        $todasLasCategorias = Categoria::pluck('id_categoria')->toArray();
        $categorias = $request->input('categorias', array_merge(['todos'], $todasLasCategorias));

        \Log::info('Filtros procesados:', [
            'fecha_inicio' => $fechaInicio,
            'fecha_final' => $fechaFinal,
            'proveedor' => $proveedorId,
            'categorias' => $categorias
        ]);

        // Debug: Ver todas las compras sin filtro de estado
        $todasLasCompras = Compra::with(['proveedor'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFinal])
            ->get();

        \Log::info('Todas las compras en el período:', [
            'total' => $todasLasCompras->count(),
            'por_estado' => $todasLasCompras->groupBy('estado')->map->count(),
            'compras' => $todasLasCompras->map(function($c) {
                return [
                    'id' => $c->id_compra,
                    'proveedor' => $c->proveedor->nombre ?? 'Sin proveedor',
                    'fecha' => $c->fecha,
                    'estado' => $c->estado,
                    'total' => $c->total
                ];
            })
        ]);

        $query = Compra::with(['proveedor', 'detalles.producto.categoria'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFinal])
            ->where('estado', false);

        if ($proveedorId && $proveedorId !== 'todos') {
            $query->where('id_proveedor', $proveedorId);
        }

        // Filtro por categorías - NO aplicamos filtro aquí, lo haremos después
        // para poder filtrar productos individuales dentro de cada compra

        $compras = $query->orderBy('fecha', 'desc')->get();

        \Log::info('Compras encontradas:', ['count' => $compras->count()]);

        // Si se solicita PDF, generar y devolver
        if ($request->has('generar_pdf')) {
            return $this->generarPDFCompras($compras, $fechaInicio, $fechaFinal);
        }

        return Inertia::render('Reportes/Compras', [
            'compras' => $compras,
            'proveedores' => Proveedor::orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'proveedor' => $proveedorId,
                'categorias' => $categorias
            ],
            'pdf' => $request->has('generar_pdf') ? $this->generarPDFComprasData($compras, $fechaInicio, $fechaFinal, $categorias) : null,
            'filename' => $request->has('generar_pdf') ? 'reporte_compras_' . date('Y-m-d_H-i-s') . '.pdf' : null
        ]);
    }

    private function generarPDFComprasData($compras, $fechaInicio, $fechaFinal, $categorias = [])
    {
        try {
            // Usar una consulta más simple sin JOINs complejos
            $detallesConProductos = collect();

            foreach ($compras as $compra) {
                $detalles = \DB::table('detalle_compras')
                    ->where('id_compra', $compra->id_compra)
                    ->get();

                foreach ($detalles as $detalle) {
                    // Obtener datos del producto
                    $producto = \DB::table('productos')
                        ->where('id_producto', $detalle->id_producto)
                        ->first();

                    // Aplicar filtro de categorías a nivel de producto
                    if (!empty($categorias) && !in_array('todos', $categorias)) {
                        if (!$producto || !in_array($producto->id_categoria, $categorias)) {
                            continue; // Saltar este producto si no está en las categorías seleccionadas
                        }
                    }

                    // Obtener datos de la categoría
                    $categoria = null;
                    if ($producto && $producto->id_categoria) {
                        $categoria = \DB::table('categorias')
                            ->where('id_categoria', $producto->id_categoria)
                            ->first();
                    }

                    // Obtener datos del modelo
                    $modelo = null;
                    if ($producto && $producto->id_modelo) {
                        $modelo = \DB::table('modelos')
                            ->where('id_modelo', $producto->id_modelo)
                            ->first();
                    }

                    // Obtener marca a partir del modelo
                    $marca = null;
                    if ($modelo && isset($modelo->id_marca)) {
                        $marca = \DB::table('marcas')->where('id_marca', $modelo->id_marca)->first();
                    }

                    // Obtener datos del proveedor
                    $proveedor = \DB::table('proveedores')
                        ->where('id_proveedor', $compra->id_proveedor)
                        ->first();

                    // Crear objeto con todos los datos
                    $marcaNombre = $marca ? ($marca->nombre ?? $marca->descripcion ?? '') : '';
                    $modeloNombre = $modelo ? ($modelo->nombre ?? $modelo->descripcion ?? '') : '';
                    $productoDesc = $producto && $producto->descripcion ? $producto->descripcion : '';
                    $marcaModeloDescripcion = trim(($marcaNombre ? $marcaNombre : '') . ($modeloNombre ? '/'.$modeloNombre : '') . ($productoDesc ? ' - '.$productoDesc : ''));

                    $detalleCompleto = (object) [
                        'id_detalle_compra' => $detalle->id_detalle_compra,
                        'id_compra' => $detalle->id_compra,
                        'id_producto' => $detalle->id_producto,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'producto_nombre' => $producto ? ($producto->descripcion ?? 'Producto no encontrado') : 'Producto no encontrado',
                        'modelo_descripcion' => $marcaModeloDescripcion ?: ($productoDesc ?: 'Sin información'),
                        'categoria_nombre' => $categoria ? ($categoria->nombre ?? $categoria->descripcion ?? 'Sin categoría') : 'Sin categoría',
                        'proveedor_nombre' => $proveedor ? ($proveedor->nombre ?? $proveedor->descripcion ?? 'Sin proveedor') : 'Sin proveedor',
                        'compra_fecha' => $compra->fecha
                    ];

                    $detallesConProductos->push($detalleCompleto);
                }
            }

            // Agrupar detalles por compra
            $comprasConDetalles = $compras->map(function($compra) use ($detallesConProductos) {
                $compra->detalles_completos = $detallesConProductos->where('id_compra', $compra->id_compra);
                return $compra;
            });

            // Calcular totales después del filtrado
            $totalCompras = $detallesConProductos->sum(function($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
            $cantidadCompras = $comprasConDetalles->filter(function($compra) {
                return $compra->detalles_completos->count() > 0;
            })->count();

            // Generar HTML para el PDF
            $html = view('reportes.compras-pdf', [
                'compras' => $comprasConDetalles,
                'fechaInicio' => $fechaInicio,
                'fechaFinal' => $fechaFinal,
                'totalCompras' => $totalCompras,
                'cantidadCompras' => $cantidadCompras,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ])->render();

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Convertir a base64
            $pdfContent = $pdf->output();
            return base64_encode($pdfContent);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF de compras: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mostrar la página de reportes de ventas
     */
    public function ventas(Request $request)
    {
        \Log::info('Método ventas llamado con parámetros:', $request->all());

        // Fechas por defecto: primer día y último día del mes actual
        $primerDiaMes = date('Y-m-01');
        $ultimoDiaMes = date('Y-m-t');
        
        $fechaInicio = $request->input('fecha_inicio', $primerDiaMes);
        $fechaFinal = $request->input('fecha_final', $ultimoDiaMes);
        $usuarioId = $request->input('usuario', 'todos'); // Todos los usuarios por defecto

        // Obtener todas las categorías para marcarlas por defecto
        $todasLasCategorias = Categoria::pluck('id_categoria')->toArray();
        $categorias = $request->input('categorias', array_merge(['todos'], $todasLasCategorias));

        \Log::info('Filtros procesados:', [
            'fecha_inicio' => $fechaInicio,
            'fecha_final' => $fechaFinal,
            'usuario' => $usuarioId,
            'categorias' => $categorias
        ]);

        $query = Venta::with(['cliente', 'usuario', 'detalles.producto.categoria'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFinal])
            ->where('estado', false);

        if ($usuarioId && $usuarioId !== 'todos') {
            $query->where('id_usuario', $usuarioId);
        }

        if (!empty($categorias) && !in_array('todos', $categorias)) {
            $query->whereHas('detalles.producto', function($q) use ($categorias) {
                $q->whereIn('id_categoria', $categorias);
            });
        }

        $ventas = $query->orderBy('fecha', 'desc')->get();

        \Log::info('Ventas encontradas:', ['count' => $ventas->count()]);

        // Si se solicita PDF, generar y devolver
        if ($request->has('generar_pdf')) {
            return $this->generarPDFVentas($ventas, $fechaInicio, $fechaFinal);
        }

        return Inertia::render('Reportes/Ventas', [
            'ventas' => $ventas,
            'usuarios' => User::orderBy('name')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'usuario' => $usuarioId,
                'categorias' => $categorias
            ],
            'pdf' => $request->has('generar_pdf') ? $this->generarPDFVentasData($ventas, $fechaInicio, $fechaFinal, $categorias) : null,
            'filename' => $request->has('generar_pdf') ? 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.pdf' : null
        ]);
    }

    private function generarPDFVentasData($ventas, $fechaInicio, $fechaFinal, $categorias = [])
    {
        try {
            // Usar una consulta más simple sin JOINs complejos
            $detallesConProductos = collect();

            foreach ($ventas as $venta) {
                $detalles = \DB::table('detalle_ventas')
                    ->where('id_venta', $venta->id_venta)
                    ->get();

                foreach ($detalles as $detalle) {
                    // Obtener datos del producto
                    $producto = \DB::table('productos')
                        ->where('id_producto', $detalle->id_producto)
                        ->first();

                    // Aplicar filtro de categorías a nivel de producto
                    if (!empty($categorias) && !in_array('todos', $categorias)) {
                        if (!$producto || !in_array($producto->id_categoria, $categorias)) {
                            continue; // Saltar este producto si no está en las categorías seleccionadas
                        }
                    }

                    // Obtener datos de la categoría
                    $categoria = null;
                    if ($producto && $producto->id_categoria) {
                        $categoria = \DB::table('categorias')
                            ->where('id_categoria', $producto->id_categoria)
                            ->first();
                    }

                    // Obtener datos del modelo
                    $modelo = null;
                    $marca = null;
                    if ($producto && $producto->id_modelo) {
                        $modelo = \DB::table('modelos')
                            ->where('id_modelo', $producto->id_modelo)
                            ->first();
                        
                        // Obtener la marca del modelo
                        if ($modelo && $modelo->id_marca) {
                            $marca = \DB::table('marcas')
                                ->where('id_marca', $modelo->id_marca)
                                ->first();
                        }
                    }

                    // Obtener datos del cliente
                    $cliente = \DB::table('clientes')
                        ->where('id_cliente', $venta->id_cliente)
                        ->first();

                    // Construir modelo_descripcion incluyendo la marca: "Marca Modelo - descripción"
                    $modeloDescripcion = '';
                    if ($modelo) {
                        $nombreModelo = $modelo->nombre ?? $modelo->descripcion ?? 'Sin modelo';
                        
                        // Agregar marca si existe
                        if ($marca && $marca->nombre) {
                            $modeloDescripcion = $marca->nombre . ' ' . $nombreModelo;
                        } else {
                            $modeloDescripcion = $nombreModelo;
                        }
                        
                        // Agregar descripción del producto si existe
                        if ($producto && $producto->descripcion) {
                            $modeloDescripcion .= ' - ' . $producto->descripcion;
                        }
                    } else {
                        // Si no hay modelo, mostrar solo la descripción del producto
                        $modeloDescripcion = $producto && $producto->descripcion ? $producto->descripcion : 'Sin información';
                    }

                    $detalleCompleto = (object) [
                        'id_venta' => $venta->id_venta,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'producto_nombre' => $producto ? ($producto->descripcion ?? 'Sin nombre') : 'Producto no encontrado',
                        'modelo_descripcion' => $modeloDescripcion,
                        'categoria_nombre' => $categoria ? ($categoria->nombre ?? $categoria->descripcion ?? 'Sin categoría') : 'Sin categoría',
                        'cliente_nombre' => $cliente ? ($cliente->nombre ?? 'Sin cliente') : 'Sin cliente',
                        'cliente_apellidos' => $cliente ? ($cliente->apellidos ?? '') : '',
                        'venta_fecha' => $venta->fecha
                    ];

                    $detallesConProductos->push($detalleCompleto);
                }
            }

            // Agrupar detalles por venta
            $ventasConDetalles = $ventas->map(function($venta) use ($detallesConProductos) {
                $venta->detalles_completos = $detallesConProductos->where('id_venta', $venta->id_venta);
                return $venta;
            });

            // Calcular totales después del filtrado
            $totalVentas = $detallesConProductos->sum(function($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
            $cantidadVentas = $ventasConDetalles->filter(function($venta) {
                return $venta->detalles_completos->count() > 0;
            })->count();

            // Generar HTML para el PDF
            $html = view('reportes.ventas-pdf', [
                'ventas' => $ventasConDetalles,
                'fechaInicio' => $fechaInicio,
                'fechaFinal' => $fechaFinal,
                'totalVentas' => $totalVentas,
                'cantidadVentas' => $cantidadVentas,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ])->render();

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Convertir a base64
            $pdfContent = $pdf->output();
            return base64_encode($pdfContent);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF de ventas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generar reporte de compras (PDF)
     */
    public function generarReporteCompras(Request $request)
    {
        try {
            \Log::info('Iniciando generación de reporte de compras');

            // Obtener filtros del request
            $filtros = $request->only(['fecha_desde', 'fecha_hasta', 'proveedor', 'ordenar_por', 'orden']);

            // Valores por defecto
            $fechaDesde = $filtros['fecha_desde'] ?? null;
            $fechaHasta = $filtros['fecha_hasta'] ?? null;
            $proveedor = $filtros['proveedor'] ?? null;
            $ordenarPor = $filtros['ordenar_por'] ?? 'fecha';
            $orden = $filtros['orden'] ?? 'desc';

            // Construir query base
            $query = Compra::with(['proveedor', 'detalles.producto', 'detalles.producto.categoria', 'detalles.producto.marca', 'detalles.producto.modelo']);

            // Aplicar filtros de fecha
            if ($fechaDesde) {
                $query->where('fecha', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('fecha', '<=', $fechaHasta);
            }

            // Filtro por proveedor
            if ($proveedor) {
                $query->where('id_proveedor', $proveedor);
            }

            // Ordenamiento
            if ($ordenarPor === 'fecha') {
                $query->orderBy('fecha', $orden);
            } elseif ($ordenarPor === 'total') {
                $query->orderByRaw('(SELECT SUM(cantidad * precio_unitario) FROM detalle_compras WHERE detalle_compras.id_compra = compras.id_compra) ' . $orden);
            } else {
                $query->orderBy($ordenarPor, $orden);
            }

            // Limitar resultados para evitar problemas de memoria
            $compras = $query->limit(100)->get();

            if ($compras->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron compras en el rango de fechas especificado'
                ], 400);
            }

            // Calcular totales
            $totalCompras = $compras->count();
            $totalMonto = $compras->sum(function($compra) {
                return $compra->detalles->sum('total_parcial');
            });

            // Generar HTML del reporte
            $html = view('reportes.compras', [
                'compras' => $compras,
                'filtros' => $filtros,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'total_compras' => $totalCompras,
                'total_monto' => $totalMonto
            ])->render();

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_compras_' . now()->format('Y-m-d_H-i-s') . '.pdf',
                'datos' => [
                    'total_compras' => $totalCompras,
                    'monto_total' => $totalMonto
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteCompras: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte de ventas (PDF)
     */
    public function generarReporteVentas(Request $request)
    {
        try {
            \Log::info('Iniciando generación de reporte de ventas');

            // Obtener filtros del request
            $filtros = $request->only(['fecha_desde', 'fecha_hasta', 'cliente', 'ordenar_por', 'orden', 'monto_minimo', 'monto_maximo', 'categorias']);

            // Valores por defecto
            $fechaDesde = $filtros['fecha_desde'] ?? null;
            $fechaHasta = $filtros['fecha_hasta'] ?? null;
            $cliente = $filtros['cliente'] ?? null;
            $ordenarPor = $filtros['ordenar_por'] ?? 'fecha';
            $orden = $filtros['orden'] ?? 'desc';
            $montoMinimo = $filtros['monto_minimo'] ?? null;
            $montoMaximo = $filtros['monto_maximo'] ?? null;
            $categorias = $filtros['categorias'] ?? [];

            // Construir query base
            $query = Venta::with(['cliente', 'detalles.producto', 'detalles.producto.categoria', 'detalles.producto.marca', 'detalles.producto.modelo', 'usuario'])
                ->where(function($q) {
                    $q->where('estado', false)->orWhereNull('estado');
                });

            // Aplicar filtros de fecha
            if ($fechaDesde) {
                $query->where('fecha', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('fecha', '<=', $fechaHasta);
            }

            // Filtro por cliente
            if ($cliente) {
                $query->where('id_cliente', $cliente);
            }

            // Filtro por categorías
            if (!empty($categorias)) {
                $query->whereHas('detalles.producto', function($q) use ($categorias) {
                    $q->whereIn('id_categoria', $categorias);
                });
            }

            // Ordenamiento
            if ($ordenarPor === 'fecha') {
                $query->orderBy('fecha', $orden);
            } elseif ($ordenarPor === 'total') {
                $query->orderByRaw('(SELECT SUM(cantidad * precio_unitario) FROM detalle_ventas WHERE detalle_ventas.id_venta = ventas.id_venta) ' . $orden);
            } else {
                $query->orderBy($ordenarPor, $orden);
            }

            // Limitar resultados para evitar problemas de memoria
            $ventas = $query->limit(100)->get();

            // Aplicar filtros de monto después de obtener los datos (para calcular totales)
            if ($montoMinimo !== null || $montoMaximo !== null) {
                $ventas = $ventas->filter(function($venta) use ($montoMinimo, $montoMaximo) {
                    $total = $venta->detalles->sum('total_parcial');
                    if ($montoMinimo !== null && $total < $montoMinimo) return false;
                    if ($montoMaximo !== null && $total > $montoMaximo) return false;
                    return true;
                });
            }

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron ventas en el rango de fechas especificado'
                ], 400);
            }

            // Calcular totales
            $totalVentas = $ventas->count();
            $totalMonto = $ventas->sum(function($venta) {
                return $venta->detalles->sum('total_parcial');
            });

            // Generar HTML del reporte
            $html = view('reportes.ventas', [
                'ventas' => $ventas,
                'filtros' => $filtros,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'total_ventas' => $totalVentas,
                'total_monto' => $totalMonto
            ])->render();

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_ventas_' . now()->format('Y-m-d_H-i-s') . '.pdf',
                'datos' => [
                    'total_ventas' => $totalVentas,
                    'monto_total' => $totalMonto
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteVentas: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la vista de reportes de clientes
     */
    public function clientes(Request $request)
    {
        \Log::info('Accediendo a la vista de reportes de clientes');
        try {
            return Inertia::render('Reportes/Clientes');
        } catch (\Exception $e) {
            \Log::error('Error renderizando vista de reportes de clientes: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Genera el reporte de clientes en PDF
     */
    public function generarReporteClientes(Request $request)
    {
        try {
            \Log::info('Iniciando generación de reporte de clientes');
            $filtros = $request->only(['ordenar_por', 'search', 'orden']);
            \Log::info('Filtros recibidos:', $filtros);

            // Obtener clientes con filtros
            $query = Cliente::withCount('ventas');

            // Filtro de búsqueda
            if (!empty($filtros['search'])) {
                $query->where(function($q) use ($filtros) {
                    $q->where('nombre', 'like', '%' . $filtros['search'] . '%')
                      ->orWhere('apellidos', 'like', '%' . $filtros['search'] . '%')
                      ->orWhere('ci', 'like', '%' . $filtros['search'] . '%')
                      ->orWhere('telefono', 'like', '%' . $filtros['search'] . '%');
                });
            }

            // Ordenamiento
            $ordenarPor = $filtros['ordenar_por'] ?? 'nombre';
            $orden = $filtros['orden'] ?? 'asc';

            if ($ordenarPor === 'total_ventas') {
                $query->orderBy('ventas_count', $orden);
            } elseif ($ordenarPor === 'monto_total') {
                $query->orderByRaw('(SELECT COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) FROM ventas v JOIN detalle_ventas dv ON v.id_venta = dv.id_venta WHERE v.id_cliente = clientes.id_cliente) ' . $orden);
            } elseif (in_array($ordenarPor, ['nombre', 'apellidos', 'ci', 'telefono', 'created_at'])) {
                $query->orderBy($ordenarPor, $orden);
            } else {
                $query->orderBy('nombre', $orden);
            }

            $clientes = $query->get();
            \Log::info('Clientes obtenidos: ' . $clientes->count());

            // Calcular totales para cada cliente de forma más simple
            foreach ($clientes as $cliente) {
                $cliente->monto_total = 0;
                try {
                    $total = DB::table('ventas')
                        ->join('detalle_ventas', 'ventas.id_venta', '=', 'detalle_ventas.id_venta')
                        ->where('ventas.id_cliente', $cliente->id_cliente)
                        ->selectRaw('SUM(detalle_ventas.cantidad * detalle_ventas.precio_unitario) as total')
                        ->value('total');
                    $cliente->monto_total = $total ?? 0;
                } catch (\Exception $e) {
                    \Log::error('Error calculando total para cliente ' . $cliente->id_cliente . ': ' . $e->getMessage());
                    $cliente->monto_total = 0;
                }
            }
            \Log::info('Totales calculados correctamente');

            // Generar HTML del reporte
            \Log::info('Generando HTML del reporte');
            $html = view('reportes.clientes', [
                'clientes' => $clientes,
                'filtros' => $filtros,
                'fecha_generacion' => now()->format('d/m/Y H:i:s')
            ])->render();
            \Log::info('HTML generado correctamente');

            \Log::info('Cargando PDF');
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            \Log::info('PDF cargado correctamente');

            // Convertir PDF a base64 para enviar como JSON
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_clientes_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteClientes: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarReporteVentasFrontend(Request $request)
    {
        try {
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');

            \Log::info('Generando reporte de ventas para frontend');
            \Log::info('Fechas: ' . $fechaDesde . ' - ' . $fechaHasta);

            // Obtener ventas
            $ventas = Venta::with(['cliente', 'detalles'])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->where('estado', true)
                ->orderBy('fecha', 'desc')
                ->get();

            \Log::info('Ventas encontradas: ' . $ventas->count());

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron ventas en el rango de fechas especificado'
                ], 400);
            }

            // Cargar relaciones
            $ventas->load(['cliente', 'detalles']);

            // Generar HTML simple
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
        <p>Período: ' . $fechaDesde . ' - ' . $fechaHasta . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';

            $totalGeneral = 0;
            foreach ($ventas as $venta) {
                $totalVenta = $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
                $totalGeneral += $totalVenta;

                $html .= '<tr>
                    <td>' . $venta->id_venta . '</td>
                    <td>' . $venta->fecha->format('d/m/Y') . '</td>
                    <td>' . ($venta->cliente->nombre ?? 'Sin cliente') . '</td>
                    <td>Bs ' . number_format($totalVenta, 2) . '</td>
                </tr>';
            }

            $html .= '</tbody>
    </table>

    <div style="margin-top: 30px; text-align: center;">
        <p><strong>Total de ventas: ' . $ventas->count() . '</strong></p>
        <p><strong>Total general: Bs ' . number_format($totalGeneral, 2) . '</strong></p>
    </div>
</body>
</html>';

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            \Log::info('PDF generado exitosamente para frontend');

            // Devolver respuesta en el formato que espera el frontend
            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.pdf',
                'datos' => [
                    'total_ventas' => $ventas->count(),
                    'monto_total' => $totalGeneral
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteVentasFrontend: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simularLlamadaFrontend()
    {
        try {
            // Simular exactamente los datos que envía el frontend
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'fecha_desde' => '2025-03-01',
                'fecha_hasta' => '2025-10-28',
                'cliente' => null,
                'ordenar_por' => 'fecha',
                'monto_minimo' => null,
                'monto_maximo' => null,
                'categorias' => []
            ]);

            \Log::info('Simulando llamada del frontend con datos: ' . json_encode($request->all()));

            // Llamar al método que está configurado en la ruta
            $resultado = $this->generarReporteVentasSimple($request);

            // Obtener el contenido de la respuesta
            $contenido = $resultado->getContent();
            $data = json_decode($contenido, true);

            return response()->json([
                'success' => true,
                'message' => 'Simulación completada',
                'status_code' => $resultado->getStatusCode(),
                'response_data' => $data,
                'raw_content_length' => strlen($contenido)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en simulación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en simulación: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function pruebaPasoAPaso()
    {
        try {
            $resultados = [];

            // Paso 1: Verificar conexión a base de datos
            $resultados['paso_1'] = 'Verificando conexión a base de datos...';
            $ventasCount = Venta::count();
            $resultados['paso_1_resultado'] = "OK - Total de ventas en BD: {$ventasCount}";

            // Paso 2: Verificar campo estado
            $resultados['paso_2'] = 'Verificando campo estado...';
            $estadoValues = \DB::select("SELECT estado, COUNT(*) as cantidad FROM ventas GROUP BY estado");
            $resultados['paso_2_resultado'] = $estadoValues;

            // Paso 3: Probar consulta básica
            $resultados['paso_3'] = 'Probando consulta básica...';
            $ventasBasicas = Venta::limit(3)->get();
            $resultados['paso_3_resultado'] = "OK - Encontradas: " . $ventasBasicas->count() . " ventas";

            // Paso 4: Probar consulta con filtro de estado
            $resultados['paso_4'] = 'Probando consulta con filtro estado...';
            $ventasConEstado = Venta::where(function($q) {
                $q->where('estado', false)->orWhereNull('estado');
            })->limit(3)->get();
            $resultados['paso_4_resultado'] = "OK - Encontradas: " . $ventasConEstado->count() . " ventas";

            // Paso 5: Probar consulta con fechas
            $resultados['paso_5'] = 'Probando consulta con fechas...';
            $ventasConFechas = Venta::whereBetween('fecha', ['2025-01-01', '2025-12-31'])
                ->where(function($q) {
                    $q->where('estado', false)->orWhereNull('estado');
                })
                ->limit(3)->get();
            $resultados['paso_5_resultado'] = "OK - Encontradas: " . $ventasConFechas->count() . " ventas";

            // Paso 6: Probar carga de relaciones
            $resultados['paso_6'] = 'Probando carga de relaciones...';
            if ($ventasConFechas->count() > 0) {
                $venta = $ventasConFechas->first();
                $venta->load(['cliente', 'detalles']);
                $resultados['paso_6_resultado'] = "OK - Cliente: " . ($venta->cliente->nombre ?? 'Sin cliente') . ", Detalles: " . $venta->detalles->count();
            } else {
                $resultados['paso_6_resultado'] = "SKIP - No hay ventas para probar relaciones";
            }

            // Paso 7: Probar generación de HTML
            $resultados['paso_7'] = 'Probando generación de HTML...';
            $html = '<h1>Test HTML</h1><p>Esto es una prueba</p>';
            $resultados['paso_7_resultado'] = "OK - HTML generado: " . strlen($html) . " caracteres";

            // Paso 8: Probar generación de PDF
            $resultados['paso_8'] = 'Probando generación de PDF...';
            try {
                $pdf = Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');
                $pdfContent = $pdf->output();
                $resultados['paso_8_resultado'] = "OK - PDF generado: " . strlen($pdfContent) . " bytes";
            } catch (\Exception $e) {
                $resultados['paso_8_resultado'] = "ERROR - " . $e->getMessage();
            }

            return response()->json([
                'success' => true,
                'message' => 'Prueba paso a paso completada',
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en prueba paso a paso: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function generarReporteVentasSinEstado(Request $request)
    {
        try {
            \Log::info('Iniciando generación de reporte SIN filtro de estado');

            // Obtener filtros básicos
            $fechaDesde = $request->input('fecha_desde', '2025-01-01');
            $fechaHasta = $request->input('fecha_hasta', date('Y-m-d'));

            // Consulta SIN filtro de estado
            $ventas = Venta::whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->orderBy('fecha', 'desc')
                ->limit(50)
                ->get();

            \Log::info('Ventas encontradas SIN filtro estado: ' . $ventas->count());

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron ventas en el rango de fechas especificado'
                ], 400);
            }

            // Cargar relaciones de forma individual
            $ventas->load(['cliente', 'detalles']);

            // Generar HTML simple
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas (Sin Filtro Estado)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas (Sin Filtro Estado)</h1>
        <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
        <p>Período: ' . $fechaDesde . ' - ' . $fechaHasta . '</p>
        <p><strong>NOTA: Este reporte incluye TODAS las ventas, sin filtrar por estado</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';

            $totalGeneral = 0;
            foreach ($ventas as $venta) {
                $totalVenta = $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
                $totalGeneral += $totalVenta;

                $estadoTexto = $venta->estado === null ? 'NULL' : ($venta->estado ? 'true' : 'false');

                $html .= '<tr>
                    <td>' . $venta->id_venta . '</td>
                    <td>' . $venta->fecha->format('d/m/Y') . '</td>
                    <td>' . ($venta->cliente->nombre ?? 'Sin cliente') . '</td>
                    <td>' . $estadoTexto . '</td>
                    <td>Bs ' . number_format($totalVenta, 2) . '</td>
                </tr>';
            }

            $html .= '</tbody>
    </table>

    <div style="margin-top: 30px; text-align: center;">
        <p><strong>Total de ventas: ' . $ventas->count() . '</strong></p>
        <p><strong>Total general: Bs ' . number_format($totalGeneral, 2) . '</strong></p>
    </div>
</body>
</html>';

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            \Log::info('PDF generado exitosamente SIN filtro estado');

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_ventas_sin_estado_' . date('Y-m-d_H-i-s') . '.pdf',
                'datos' => [
                    'total_ventas' => $ventas->count(),
                    'monto_total' => $totalGeneral
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteVentasSinEstado: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
    public function diagnosticarCampoEstado()
    {
        try {
            $diagnostico = [];

            // Paso 1: Verificar estructura de la tabla
            $columnas = \DB::select("DESCRIBE ventas");
            $diagnostico['columnas_tabla'] = $columnas;

            // Paso 2: Verificar valores del campo estado
            $valoresEstado = \DB::select("SELECT estado, COUNT(*) as cantidad FROM ventas GROUP BY estado");
            $diagnostico['valores_estado'] = $valoresEstado;

            // Paso 3: Probar consulta sin filtro de estado
            $ventasSinFiltro = Venta::count();
            $diagnostico['ventas_sin_filtro'] = $ventasSinFiltro;

            // Paso 4: Probar consulta con filtro estado = false
            $ventasEstadoFalse = Venta::where('estado', false)->count();
            $diagnostico['ventas_estado_false'] = $ventasEstadoFalse;

            // Paso 5: Probar consulta con filtro estado = true
            $ventasEstadoTrue = Venta::where('estado', true)->count();
            $diagnostico['ventas_estado_true'] = $ventasEstadoTrue;

            // Paso 6: Probar consulta con estado IS NULL
            $ventasEstadoNull = Venta::whereNull('estado')->count();
            $diagnostico['ventas_estado_null'] = $ventasEstadoNull;

            // Paso 7: Probar consulta con estado IS NOT NULL
            $ventasEstadoNotNull = Venta::whereNotNull('estado')->count();
            $diagnostico['ventas_estado_not_null'] = $ventasEstadoNotNull;

            // Paso 8: Obtener algunas ventas de ejemplo
            $ventasEjemplo = Venta::select('id_venta', 'fecha', 'estado')->limit(5)->get();
            $diagnostico['ventas_ejemplo'] = $ventasEjemplo;

            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico del campo estado completado',
                'diagnostico' => $diagnostico
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en diagnóstico: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    public function generarReporteVentasSimple(Request $request)
    {
        try {
            \Log::info('Iniciando generación simplificada de reporte de ventas');

            // Obtener filtros básicos
            $fechaDesde = $request->input('fecha_desde', '2025-01-01');
            $fechaHasta = $request->input('fecha_hasta', date('Y-m-d'));

            // Consulta con manejo robusto del campo estado
            $ventas = Venta::where(function($query) {
                    $query->where('estado', false)
                          ->orWhereNull('estado');
                })
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->orderBy('fecha', 'desc')
                ->limit(50) // Limitar para evitar problemas de memoria
                ->get();

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron ventas en el rango de fechas especificado'
                ], 400);
            }

            // Cargar relaciones de forma individual para evitar problemas
            $ventas->load(['cliente', 'detalles']);

            // Generar HTML simple sin vista Blade compleja
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
        <p>Período: ' . $fechaDesde . ' - ' . $fechaHasta . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';

            $totalGeneral = 0;
            foreach ($ventas as $venta) {
                $totalVenta = $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
                $totalGeneral += $totalVenta;

                $html .= '<tr>
                    <td>' . $venta->id_venta . '</td>
                    <td>' . $venta->fecha->format('d/m/Y') . '</td>
                    <td>' . ($venta->cliente->nombre ?? 'Sin cliente') . '</td>
                    <td>Bs ' . number_format($totalVenta, 2) . '</td>
                </tr>';
            }

            $html .= '</tbody>
    </table>

    <div style="margin-top: 30px; text-align: center;">
        <p><strong>Total de ventas: ' . $ventas->count() . '</strong></p>
        <p><strong>Total general: Bs ' . number_format($totalGeneral, 2) . '</strong></p>
    </div>
</body>
</html>';

            // Generar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            \Log::info('PDF generado exitosamente');

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_ventas_simple_' . date('Y-m-d_H-i-s') . '.pdf',
                'datos' => [
                    'total_ventas' => $ventas->count(),
                    'monto_total' => $totalGeneral
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en generarReporteVentasSimple: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
    public function testReporteVentas()
    {
        try {
            \Log::info('Iniciando prueba del reporte de ventas');

            // Simular request con filtros básicos
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'fecha_desde' => '2025-01-01',
                'fecha_hasta' => date('Y-m-d'),
                'ordenar_por' => 'fecha'
            ]);

            // Llamar al método principal
            $resultado = $this->generarReporteVentas($request);
            $data = json_decode($resultado->getContent(), true);

            if ($data['success']) {
                \Log::info('Prueba exitosa: PDF generado correctamente');
                return response()->json([
                    'success' => true,
                    'message' => 'Reporte de ventas funcionando correctamente',
                    'datos' => $data['datos'] ?? null,
                    'filename' => $data['filename'] ?? null
                ]);
            } else {
                \Log::warning('Prueba fallida: ' . $data['message']);
                return response()->json([
                    'success' => false,
                    'message' => 'Error en la prueba: ' . $data['message']
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Error en testReporteVentas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la prueba: ' . $e->getMessage()
            ], 500);
        }
    }
    public function primeraFechaCompra()
    {
        try {
            $primeraCompra = \App\Models\Compra::orderBy('fecha', 'asc')->first();

            if ($primeraCompra) {
                return response()->json([
                    'success' => true,
                    'fecha_primera_compra' => $primeraCompra->fecha
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay compras registradas'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener primera fecha de compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la primera fecha de venta
     */
    public function primeraFechaVenta()
    {
        try {
            \Log::info('Buscando primera fecha de venta...');
            $primeraVenta = \App\Models\Venta::orderBy('fecha', 'asc')->first();
            \Log::info('Primera venta encontrada:', $primeraVenta ? $primeraVenta->toArray() : 'null');

            if ($primeraVenta) {
                \Log::info('Devolviendo fecha:', $primeraVenta->fecha);
                return response()->json([
                    'success' => true,
                    'fecha_primera_venta' => $primeraVenta->fecha
                ]);
            } else {
                \Log::info('No hay ventas registradas');
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ventas registradas'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error en primeraFechaVenta:', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener primera fecha de venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarPDFCompras(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', '2020-01-01');
            $fechaFinal = $request->input('fecha_final', date('Y-m-d'));
            $proveedorId = $request->input('proveedor', 'todos');

            // Obtener todas las categorías para marcarlas por defecto
            $todasLasCategorias = Categoria::pluck('id_categoria')->toArray();
            $categorias = $request->input('categorias', array_merge(['todos'], $todasLasCategorias));

            // Si categorias viene como JSON string, decodificarlo
            if (is_string($categorias)) {
                $categorias = json_decode($categorias, true) ?: [];
            }

            \Log::info('Generando PDF Compras con parámetros:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'proveedor' => $proveedorId,
                'categorias' => $categorias
            ]);

            // Primero verificar si hay compras en general
            $totalComprasDB = Compra::count();
            \Log::info('Total de compras en DB:', ['total' => $totalComprasDB]);

            $query = Compra::with(['proveedor', 'detalles.producto.categoria'])
                ->whereBetween('fecha', [$fechaInicio, $fechaFinal])
                ->where('estado', false);

            if ($proveedorId && $proveedorId !== 'todos') {
                $query->where('id_proveedor', $proveedorId);
            }

            // Filtro por categorías - NO aplicamos filtro aquí, lo haremos después
            // para poder filtrar productos individuales dentro de cada compra

            $compras = $query->orderBy('fecha', 'desc')->get();

            \Log::info('Compras encontradas:', [
                'cantidad' => $compras->count(),
                'compras' => $compras->map(function($c) {
                    return [
                        'id' => $c->id_compra,
                        'proveedor' => $c->proveedor->nombre ?? 'Sin proveedor',
                        'fecha' => $c->fecha,
                        'total' => $c->total
                    ];
                })
            ]);

            $pdfData = $this->generarPDFComprasData($compras, $fechaInicio, $fechaFinal, $categorias);

            if ($pdfData) {
                return response()->json([
                    'success' => true,
                    'pdf' => $pdfData,
                    'filename' => 'reporte_compras_' . date('Y-m-d_H-i-s') . '.pdf'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar el PDF'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error en generarPDFCompras: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarPDFVentas(Request $request)
    {
        try {
            // Fechas por defecto: primer día y último día del mes actual
            $primerDiaMes = date('Y-m-01');
            $ultimoDiaMes = date('Y-m-t');
            
            $fechaInicio = $request->input('fecha_inicio', $primerDiaMes);
            $fechaFinal = $request->input('fecha_final', $ultimoDiaMes);
            $usuarioId = $request->input('usuario', 'todos'); // Todos los usuarios por defecto

            // Obtener todas las categorías para marcarlas por defecto
            $todasLasCategorias = Categoria::pluck('id_categoria')->toArray();
            $categorias = $request->input('categorias', array_merge(['todos'], $todasLasCategorias));

            // Si categorias viene como JSON string, decodificarlo
            if (is_string($categorias)) {
                $categorias = json_decode($categorias, true) ?: [];
            }

            $query = Venta::with(['cliente', 'usuario', 'detalles.producto.categoria'])
                ->whereBetween('fecha', [$fechaInicio, $fechaFinal])
                ->where('estado', false);

            if ($usuarioId && $usuarioId !== 'todos') {
                $query->where('id_usuario', $usuarioId);
            }

            if (!empty($categorias) && !in_array('todos', $categorias)) {
                $query->whereHas('detalles.producto', function($q) use ($categorias) {
                    $q->whereIn('id_categoria', $categorias);
                });
            }

            $ventas = $query->orderBy('fecha', 'desc')->get();

            $pdfData = $this->generarPDFVentasData($ventas, $fechaInicio, $fechaFinal, $categorias);

            if ($pdfData) {
                return response()->json([
                    'success' => true,
                    'pdf' => $pdfData,
                    'filename' => 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.pdf'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar el PDF'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error en generarPDFVentas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

}
