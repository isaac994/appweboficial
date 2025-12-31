<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\DetalleCompra;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas básicas
        $stats = [
            'total_productos' => Producto::count(),
            'total_categorias' => Categoria::count(),
            'total_marcas' => Marca::count(),
            'total_proveedores' => Proveedor::count(),
            'total_clientes' => Cliente::count(),
        ];

        // Estadísticas de ventas del día - excluir ventas eliminadas
        $driver = DB::connection()->getDriverName();
        $hoyDate = Carbon::today()->format('Y-m-d');
        $ventasHoyQuery = Venta::where('estado', false);
        if ($driver === 'sqlite') {
            $ventasHoyQuery->whereRaw("date(fecha) = ?", [$hoyDate]);
        } else {
            $ventasHoyQuery->whereRaw("DATE(fecha) = ?", [$hoyDate]);
        }
        $ventas_hoy = $ventasHoyQuery->get();

        $ganancias_hoy = $ventas_hoy->sum('total');
        $ventas_cantidad_hoy = $ventas_hoy->count();

        // Variable de fecha solo con día para consultas whereDate
        $hoy = Carbon::today();

        // (opcional) Estadísticas de la semana para el gráfico ya se calculan aparte
        $ganancias_semana = 0;

        // Productos más vendidos del día - excluir ventas eliminadas
        $productos_mas_vendidos = DetalleVenta::join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
            ->join('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
            ->join('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
            ->leftJoin('marcas', 'modelos.id_marca', '=', 'marcas.id_marca')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.estado', false) // Solo ventas activas
            ->select('modelos.nombre', 'marcas.nombre as marca', 'productos.precio_venta', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.id_producto', 'modelos.nombre', 'marcas.nombre', 'productos.precio_venta')
            ->orderBy('total_vendido', 'desc')
            ->limit(3)
            ->get();

        // Estadísticas mejoradas
        $stats_mejoradas = [
            'ganancias_hoy' => $ganancias_hoy,
            'ventas_cantidad_hoy' => $ventas_cantidad_hoy,
            'ganancias_semana' => $ganancias_semana,
            'productos_mas_vendidos' => $productos_mas_vendidos,
        ];

        // Productos de las últimas compras en la categoría "celulares"
        $categoria_celulares = Categoria::where('nombre', 'like', '%celular%')->first();

        if ($categoria_celulares) {
            // Obtener los IDs de productos de las últimas compras en la categoría celulares - excluir compras eliminadas
            $productos_ids = DetalleCompra::join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                ->join('productos', 'detalle_compras.id_producto', '=', 'productos.id_producto')
                ->where('productos.id_categoria', $categoria_celulares->id_categoria)
                ->where('compras.estado', false) // Solo compras activas
                ->orderBy('compras.fecha', 'desc')
                ->limit(4)
                ->pluck('productos.id_producto')
                ->unique();

            // Obtener los productos con sus relaciones
            $productos_recientes = Producto::with(['categoria', 'marca', 'modelo'])
                ->whereIn('id_producto', $productos_ids)
                ->get()
                ->map(function ($producto) {
                    $producto->estado_disponible = $producto->estado_disponible;
                    return $producto;
                });
        } else {
            // Si no existe la categoría celulares, mostrar los 4 más recientes
            $productos_recientes = Producto::with(['categoria', 'marca', 'modelo'])
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(function ($producto) {
                    $producto->estado_disponible = $producto->estado_disponible;
                    return $producto;
                });
        }

        $clientes_recientes = Cliente::orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Calcular datos de ventas semanales
        $ventas_semanales = $this->calcularVentasSemanales(0);
        $ventas_semana_anterior = $this->calcularVentasSemanales(1);
        $ventas_semana_anterior2 = $this->calcularVentasSemanales(2);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'stats_mejoradas' => $stats_mejoradas,
            'productos_recientes' => $productos_recientes,
            'clientes_recientes' => $clientes_recientes,
            'ventas_semanales' => $ventas_semanales,
            'ventas_semana_anterior' => $ventas_semana_anterior,
            'ventas_semana_anterior2' => $ventas_semana_anterior2,
        ]);
    }

    /**
     * Calcula los datos de ventas para una semana específica
     * @param int $semanasAtras Número de semanas hacia atrás (0 = semana actual)
     * @return array
     */
    private function calcularVentasSemanales($semanasAtras = 0)
    {
        // Calcular las fechas de inicio y fin de la semana
        $inicioSemana = Carbon::now()->subWeeks($semanasAtras)->startOfWeek();
        $finSemana = Carbon::now()->subWeeks($semanasAtras)->endOfWeek();

        // Días de la semana en español
        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $labels = [];
        $data = [];

        // Obtener ventas de la semana
        $ventas = Venta::whereBetween('fecha', [$inicioSemana, $finSemana])
            ->where('estado', false)
            ->get();

        // Calcular ventas por día
        $totalSemana = 0;
        $mayorVenta = 0;
        $diaMayorVenta = '';

        // Recorrer cada día de la semana
        for ($i = 0; $i < 7; $i++) {
            $fecha = $inicioSemana->copy()->addDays($i);
            $ventasDia = $ventas->filter(function ($venta) use ($fecha) {
                return Carbon::parse($venta->fecha)->isSameDay($fecha);
            });

            $ventasDiaTotal = $ventasDia->sum('total');
            $totalSemana += $ventasDiaTotal;

            if ($ventasDiaTotal > $mayorVenta) {
                $mayorVenta = $ventasDiaTotal;
                $diaMayorVenta = $diasSemana[$fecha->dayOfWeek];
            }

            $labels[] = $diasSemana[$fecha->dayOfWeek];
            $data[] = $ventasDiaTotal;
        }

        $promedioDiario = count($data) > 0 ? $totalSemana / count($data) : 0;

        return [
            'labels' => $labels,
            'data' => $data,
            'total_semana' => $totalSemana,
            'promedio_diario' => $promedioDiario,
            'dia_mayor_venta' => $diaMayorVenta,
            'mayor_venta' => $mayorVenta,
            'fecha_inicio' => $inicioSemana->format('Y-m-d'),
            'fecha_fin' => $finSemana->format('Y-m-d'),
            'semanas_atras' => $semanasAtras,
        ];
    }
}
