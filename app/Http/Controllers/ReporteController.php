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
        $query = Producto::with(['categoria', 'marca']);

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
        $ordenarPor = $request->get('ordenar_por', 'nombre');
        $orden = $request->get('orden', 'asc');

        if ($ordenarPor === 'stock_disponible') {
            // Ordenar por stock disponible (calculado dinámicamente)
            $query->orderByRaw('
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) -
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
            ' . $orden);
        } else {
            $query->orderBy($ordenarPor, $orden);
        }

        // Obtener productos paginados
        $productos = $query->paginate(20);

        // Calcular stock disponible para cada producto
        $productos->getCollection()->transform(function ($producto) {
            $stockComprado = DB::table('detalle_compras')
                ->where('id_producto', $producto->id_producto)
                ->sum('cantidad');

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
     * Generar reporte de productos (PDF)
     */
    public function generarReporteProductos(Request $request)
    {
        try {
            // Obtener filtros del request
            $filtros = $request->only(['categoria', 'marca', 'estado', 'ordenar_por', 'orden']);

            // Construir la consulta base
            $query = Producto::with(['categoria', 'marca']);

            // Filtro por categoría
            if (!empty($filtros['categoria'])) {
                $query->where('id_categoria', $filtros['categoria']);
            }

            // Filtro por marca
            if (!empty($filtros['marca'])) {
                $query->where('id_marca', $filtros['marca']);
            }

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'disponible') {
                    $query->whereRaw('
                        (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) >
                        (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
                    ');
                } elseif ($filtros['estado'] === 'agotado') {
                    $query->whereRaw('
                        (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) <=
                        (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
                    ');
                }
            }

                        // Ordenamiento (siempre ascendente)
            $ordenarPor = $filtros['ordenar_por'] ?? 'nombre';

            if ($ordenarPor === 'precio_venta') {
                // Precio: de mayor a menor (descendente)
                $query->orderBy($ordenarPor, 'desc');
            } else {
                // Otros campos: ascendente (A-Z)
                $query->orderBy($ordenarPor, 'asc');
            }

            // Obtener productos con filtros aplicados
            $productos = $query->get();

            // Generar PDF
            $html = view('reportes.productos', [
                'productos' => $productos,
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
                'filename' => 'reporte_productos_' . now()->format('Y-m-d_H-i-s') . '.pdf'
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
        // Obtener filtros de la request
        $filtros = $request->only([
            'fecha_desde',
            'fecha_hasta',
            'proveedor',
            'ordenar_por',
            'orden'
        ]);

        // Construir la consulta base
        $query = Compra::with(['proveedor', 'detalles.producto']);

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por proveedor
        if ($request->filled('proveedor')) {
            $query->where('id_proveedor', $request->proveedor);
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'fecha');
        $orden = $request->get('orden', 'desc');

        $query->orderBy($ordenarPor, $orden);

        // Obtener compras paginadas
        $compras = $query->paginate(20);

        // Calcular totales para cada compra
        $compras->getCollection()->transform(function ($compra) {
            $compra->total_calculado = $compra->detalles->sum('total_parcial');
            return $compra;
        });

        // Obtener datos para filtros
        $proveedores = Proveedor::orderBy('nombre')->get();

        // Calcular estadísticas generales
        $totalMonto = $compras->getCollection()->sum('total_calculado');
        $totalCompras = $compras->count();

        $stats = [
            'total_compras' => $compras->total(),
            'total_monto' => $totalMonto,
            'promedio_compra' => $totalCompras > 0 ? $totalMonto / $totalCompras : 0,
            'proveedores_activos' => $compras->pluck('proveedor.nombre')->unique()->count()
        ];

        return Inertia::render('Reportes/Compras', [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'filtros' => $filtros,
            'stats' => $stats
        ]);
    }

    /**
     * Generar reporte de compras (PDF)
     */
    public function generarReporteCompras(Request $request)
    {
        try {
            // Obtener filtros del request
            $filtros = $request->only(['fecha_desde', 'fecha_hasta', 'proveedor', 'ordenar_por', 'orden']);

            // Construir la consulta base
            $query = Compra::with(['proveedor', 'detalles.producto']);

            // Filtro por fecha desde
            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }

            // Filtro por fecha hasta
            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            // Filtro por proveedor
            if (!empty($filtros['proveedor'])) {
                $query->where('id_proveedor', $filtros['proveedor']);
            }

            // Ordenamiento
            $ordenarPor = $filtros['ordenar_por'] ?? 'fecha';
            $orden = $filtros['orden'] ?? 'desc';

            $query->orderBy($ordenarPor, $orden);

            // Obtener compras con filtros aplicados
            $compras = $query->get();

            // Calcular totales
            $compras->transform(function ($compra) {
                $compra->total_calculado = $compra->detalles->sum('total_parcial');
                return $compra;
            });

            // Calcular total general
            $totalGeneral = $compras->sum(function($compra) {
                return $compra->total_calculado;
            });

            // Generar PDF
            $html = view('reportes.compras', [
                'compras' => $compras,
                'filtros' => $filtros,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'total_general' => $totalGeneral
            ])->render();

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Convertir PDF a base64 para enviar como JSON
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_compras_' . now()->format('Y-m-d_H-i-s') . '.pdf'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para verificar que DomPDF funciona
     */
    public function testPdf()
    {
        try {
            $html = '<h1>Test PDF</h1><p>Si puedes ver esto, DomPDF está funcionando correctamente.</p>';

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('test.pdf');

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

    /**
     * Mostrar la página de reportes de ventas
     */
    public function ventas(Request $request)
    {
        return Inertia::render('Reportes/Ventas');
    }

    /**
     * Generar reporte de ventas
     */
    public function generarReporteVentas(Request $request)
    {
        try {
            \Log::info('Iniciando generación de reporte de ventas');
            \Log::info('Datos recibidos: ' . json_encode($request->all()));

            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $ordenarPor = $request->input('ordenar_por', 'fecha');
            $orden = $request->input('orden', 'desc');

            \Log::info("Filtros: fecha_inicio=$fechaInicio, fecha_fin=$fechaFin, ordenar_por=$ordenarPor, orden=$orden");

            // Construir query base
            $query = Venta::with(['cliente', 'detalles.producto', 'detalles.producto.categoria', 'detalles.producto.marca', 'usuario']);

            // Aplicar filtros de fecha si se proporcionan
            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                \Log::info("Filtro de fechas aplicado: $fechaInicio a $fechaFin");
            } elseif ($fechaInicio) {
                $query->where('fecha', '>=', $fechaInicio);
                \Log::info("Filtro de fecha inicio aplicado: $fechaInicio");
            } elseif ($fechaFin) {
                $query->where('fecha', '<=', $fechaFin);
                \Log::info("Filtro de fecha fin aplicado: $fechaFin");
            }

            // Aplicar ordenamiento
            switch ($ordenarPor) {
                case 'fecha':
                    $query->orderBy('fecha', $orden);
                    break;
                case 'total':
                    $query->orderBy('total', $orden);
                    break;
                case 'cliente':
                    $query->join('clientes', 'ventas.id_cliente', '=', 'clientes.id_cliente')
                          ->orderBy('clientes.nombre', $orden)
                          ->select('ventas.*');
                    break;
                case 'usuario':
                    $query->join('users', 'ventas.id_usuario', '=', 'users.id')
                          ->orderBy('users.name', $orden)
                          ->select('ventas.*');
                    break;
                default:
                    $query->orderBy('fecha', 'desc');
            }

            $ventas = $query->get();

            \Log::info('Ventas obtenidas: ' . $ventas->count());

            // Calcular totales y ganancias para cada venta
            $ventas->transform(function ($venta) {
                // Calcular total de la venta
                $venta->total = $venta->detalles->sum(function ($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });

                // Calcular ganancia total de la venta
                $venta->ganancia_total = $venta->detalles->sum(function ($detalle) {
                    // Obtener el precio de compra promedio del producto
                    $precioCompraPromedio = DB::table('detalle_compras')
                        ->where('id_producto', $detalle->id_producto)
                        ->avg('precio_unitario');

                    $precioCompra = $precioCompraPromedio ?? 0;
                    $precioVenta = $detalle->precio_unitario;
                    $gananciaPorUnidad = $precioVenta - $precioCompra;

                    return $detalle->cantidad * $gananciaPorUnidad;
                });

                // Calcular ganancia por detalle
                $venta->detalles->transform(function ($detalle) {
                    $precioCompraPromedio = DB::table('detalle_compras')
                        ->where('id_producto', $detalle->id_producto)
                        ->avg('precio_unitario');

                    $precioCompra = $precioCompraPromedio ?? 0;
                    $precioVenta = $detalle->precio_unitario;
                    $gananciaPorUnidad = $precioVenta - $precioCompra;

                    $detalle->precio_compra = $precioCompra;
                    $detalle->ganancia_por_unidad = $gananciaPorUnidad;
                    $detalle->ganancia_total = $detalle->cantidad * $gananciaPorUnidad;

                    return $detalle;
                });

                return $venta;
            });

            // Calcular totales generales
            $totalVentas = $ventas->sum('total');
            $totalGanancia = $ventas->sum('ganancia_total');
            $cantidadVentas = $ventas->count();

            \Log::info("Totales calculados: ventas=$totalVentas, ganancia=$totalGanancia, cantidad=$cantidadVentas");

            // Generar PDF
            $pdf = Pdf::loadView('reportes.ventas', [
                'ventas' => $ventas,
                'totalVentas' => $totalVentas,
                'totalGanancia' => $totalGanancia,
                'cantidadVentas' => $cantidadVentas,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s')
            ]);

            // Convertir PDF a base64 para enviar como JSON
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'success' => true,
                'pdf' => $base64Pdf,
                'filename' => 'reporte_ventas_' . now()->format('Y-m-d_H-i-s') . '.pdf'
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
}
