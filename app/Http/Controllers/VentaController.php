<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Solo obtener búsqueda
        $search = $request->get('search');

        $query = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->where('estado', false);

        // Si hay búsqueda, filtrar las ventas
        if ($search) {
            $query->where(function($q) use ($search) {
                // Buscar por nombre y apellidos de cliente
                $q->whereHas('cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%");
                })
                // Buscar por nombre de producto en detalles
                ->orWhereHas('detalles.producto', function($productoQuery) use ($search) {
                    $productoQuery->whereHas('modelo', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
                })
                // Buscar por descripción en detalles
                ->orWhereHas('detalles', function($detalleQuery) use ($search) {
                    $detalleQuery->where('descripcion', 'like', "%{$search}%");
                });
            });
        }
        // Si no hay búsqueda, mostrar TODAS las ventas por defecto

        $ventas = $query->orderBy('id_venta', 'desc')->get();

        // Debug: Log para verificar cuántas ventas se obtuvieron
        \Log::info('Ventas obtenidas: ' . $ventas->count() . ' | Búsqueda: ' . ($search ?: 'ninguna') . ' | Orden: ID descendente');

        // Calcular totales y ganancias para cada venta (basado en último precio de compra vigente)
        $ventas->transform(function ($venta) {
            // Usar la marca temporal más precisa disponible: created_at (con hora/min/seg) y como respaldo la fecha de la venta
            $fechaObj = null;
            if ($venta->created_at) {
                $fechaObj = $venta->created_at instanceof \Carbon\Carbon ? $venta->created_at->copy() : \Carbon\Carbon::parse($venta->created_at);
            } elseif ($venta->fecha) {
                $fechaObj = $venta->fecha instanceof \Carbon\Carbon ? $venta->fecha->copy() : \Carbon\Carbon::parse($venta->fecha);
            } else {
                $fechaObj = \Carbon\Carbon::now();
            }

            // En caso de que la columna fecha tenga solo día (00:00:00), mantener la precisión de created_at
            $fechaVenta = $fechaObj->format('Y-m-d H:i:s');
            $driver = DB::connection()->getDriverName();
            // El total se calcula dinámicamente mediante el accessor getTotalAttribute()

            // Calcular ganancia total de la venta
            $venta->ganancia_total = $venta->detalles->sum(function ($detalle) use ($fechaVenta, $driver) {
                $query = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $detalle->id_producto)
                    ->where('compras.estado', false);

                if ($driver === 'sqlite') {
                    $query->whereRaw("datetime(compras.fecha) <= datetime(?)", [$fechaVenta]);
                } else {
                    $query->where('compras.fecha', '<=', $fechaVenta);
                }

                $ultimaCompra = $query
                    ->orderBy('compras.fecha', 'desc')
                    ->orderBy('compras.id_compra', 'desc')
                    ->select('detalle_compras.precio_unitario')
                    ->first();

                $precioCompra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;
                $precioVenta = floatval($detalle->precio_unitario);
                $gananciaPorUnidad = $precioVenta - $precioCompra;

                return $detalle->cantidad * $gananciaPorUnidad;
            });

            // Calcular ganancia por detalle
            $venta->detalles->transform(function ($detalle) use ($fechaVenta, $driver) {
                $query = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $detalle->id_producto)
                    ->where('compras.estado', false);

                if ($driver === 'sqlite') {
                    $query->whereRaw("datetime(compras.fecha) <= datetime(?)", [$fechaVenta]);
                } else {
                    $query->where('compras.fecha', '<=', $fechaVenta);
                }

                $ultimaCompra = $query
                    ->orderBy('compras.fecha', 'desc')
                    ->orderBy('compras.id_compra', 'desc')
                    ->select('detalle_compras.precio_unitario')
                    ->first();

                $precioCompra = $ultimaCompra ? floatval($ultimaCompra->precio_unitario) : 0;
                $precioVenta = floatval($detalle->precio_unitario);
                $gananciaPorUnidad = $precioVenta - $precioCompra;

                $detalle->precio_compra = $precioCompra;
                $detalle->ganancia_por_unidad = $gananciaPorUnidad;
                $detalle->ganancia_total = $detalle->cantidad * $gananciaPorUnidad;

                return $detalle;
            });

            return $venta;
        });

        // Calcular totales
        $totalVentas = $ventas->sum(function ($venta) {
            return $venta->total; // Usa el accessor getTotalAttribute()
        });
        $totalGanancia = $ventas->sum('ganancia_total');
        $cantidadVentas = $ventas->count();

        \Log::info('Devolviendo datos: ventas=' . $cantidadVentas . ', search=' . ($search ?: 'ninguna'));

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'total_ventas' => $totalVentas,
            'total_ganancia' => $totalGanancia,
            'cantidad_ventas' => $cantidadVentas,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function seleccionarProductos()
    {
        // Obtener productos con sus relaciones
        $productos = Producto::with(['marca', 'modelo', 'categoria'])
            ->join('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
            ->orderBy('modelos.nombre')
            ->select('productos.*')
            ->get();

        // Agregar descripción de prueba si está vacía y calcular stock
        $productos = $productos->map(function ($producto) {
            if (empty($producto->descripcion)) {
                $producto->descripcion = 'Descripción de ' . ($producto->nombre ?? $producto->modelo?->nombre ?? 'producto');
            }

            // Calcular stock disponible manualmente (excluyendo compras eliminadas)
            $totalCompras = $producto->detallesCompra()
                ->whereHas('compra', function ($query) {
                    $query->where('estado', false); // Solo compras activas
                })
                ->sum('cantidad');
            $totalVentas = $producto->detallesVenta()->sum('cantidad');
            $producto->stock_disponible = $totalCompras - $totalVentas;

            return $producto;
        });

        // Filtrar productos con stock disponible
        $productos = $productos->filter(function ($producto) {
            return $producto->stock_disponible > 0;
        })->values();

        return Inertia::render('Ventas/SeleccionarProductos', [
            'productos' => $productos
        ]);
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();

        // Obtener productos con sus relaciones
        $productos = Producto::with(['marca', 'modelo', 'categoria'])
            ->join('modelos', 'productos.id_modelo', '=', 'modelos.id_modelo')
            ->orderBy('modelos.nombre')
            ->select('productos.*')
            ->get();

        // Filtrar productos con stock disponible usando el accessor
        $productos = $productos->filter(function ($producto) {
            return $producto->stock_disponible > 0;
        })->values();

        // Debug: Log de los productos para verificar datos
        \Log::info('=== DEBUG PRODUCTOS ===');
        foreach ($productos->take(3) as $producto) {
            \Log::info('Producto: ' . ($producto->nombre ?? 'Sin nombre'));
            \Log::info('Descripción: ' . ($producto->descripcion ?? 'Sin descripción'));
            \Log::info('Modelo: ' . ($producto->modelo?->nombre ?? 'Sin modelo'));
            \Log::info('Marca: ' . ($producto->marca?->nombre ?? 'Sin marca'));
            \Log::info('Categoría: ' . ($producto->categoria?->nombre ?? 'Sin categoría'));
            \Log::info('---');
        }
        \Log::info('Total productos: ' . $productos->count());
        \Log::info('==================');

        // Agregar descripción de prueba si está vacía y calcular último precio de compra
        $productos = $productos->map(function ($producto) {
            if (empty($producto->descripcion)) {
                $producto->descripcion = 'Descripción de ' . ($producto->nombre ?? $producto->modelo?->nombre ?? 'producto');
            }

            // Obtener el último precio de compra usando el accessor del modelo
            $producto->ultimo_precio_compra = $producto->ultimo_precio_compra;

            \Log::info('Producto: ' . $producto->modelo?->nombre . ' - Ultimo precio compra: ' . $producto->ultimo_precio_compra);

            return $producto;
        });


        // Verificar si hay datos de venta temporal en la sesión
        $ventaTemporal = session('venta_temporal');

        return Inertia::render('Ventas/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'venta_edicion' => $ventaTemporal
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
                // Verificar si hay productos smartphones en la venta
        $tieneSmartphones = false;
        if ($request->has('productos') && is_array($request->productos)) {
            foreach ($request->productos as $producto) {
                $productoObj = Producto::with('categoria')->find($producto['id_producto']);
                if ($productoObj && $productoObj->categoria && strtolower($productoObj->categoria->nombre) === 'smartphones') {
                    $tieneSmartphones = true;
                    break;
                }
            }
        }

        // Determinar si es un cliente nuevo o existente
        \Log::info('Debug - request->nuevo_cliente:', ['data' => $request->nuevo_cliente]);
        \Log::info('Debug - request->has(nuevo_cliente):', ['has' => $request->has('nuevo_cliente')]);

        $esClienteNuevo = $request->has('nuevo_cliente') &&
                         $request->nuevo_cliente &&
                         isset($request->nuevo_cliente['nombre']) &&
                         !empty($request->nuevo_cliente['nombre']);

        \Log::info('Debug - esClienteNuevo:', ['esClienteNuevo' => $esClienteNuevo]);

        // El cliente solo es obligatorio si hay smartphones
        $clienteEsObligatorio = $tieneSmartphones;

        if ($esClienteNuevo) {
            // Validación de duplicidad personalizada para el nuevo cliente
            $existingCliente = null;

            // Solo validar duplicidad si los campos no están vacíos
            if (!empty($request->nuevo_cliente['ci']) || !empty($request->nuevo_cliente['telefono'])) {
                $query = Cliente::query();

                if (!empty($request->nuevo_cliente['ci'])) {
                    $query->orWhere('ci', $request->nuevo_cliente['ci']);
                }

                if (!empty($request->nuevo_cliente['telefono'])) {
                    $query->orWhere('telefono', $request->nuevo_cliente['telefono']);
                }

                $existingCliente = $query->first();
            }

            if ($existingCliente) {
                if (!empty($request->nuevo_cliente['ci']) && $existingCliente->ci === $request->nuevo_cliente['ci']) {
                    return back()->withErrors(['nuevo_cliente.ci' => 'Ya existe un cliente con este CI'])
                        ->withInput();
                }
                if (!empty($request->nuevo_cliente['telefono']) && $existingCliente->telefono === $request->nuevo_cliente['telefono']) {
                    return back()->withErrors(['nuevo_cliente.telefono' => 'Ya existe un cliente con este teléfono'])
                        ->withInput();
                }
            }

            // Validar datos del nuevo cliente
            $reglasCliente = [
                'nuevo_cliente.nombre' => 'nullable|string|max:100',
                'nuevo_cliente.apellidos' => 'nullable|string|max:100',
                'nuevo_cliente.ci' => 'nullable|string|max:20',
                'nuevo_cliente.telefono' => 'nullable|string|max:25'
            ];

            $mensajesCliente = [];

            // Si hay smartphones, el cliente es obligatorio
            if ($clienteEsObligatorio) {
                $reglasCliente['nuevo_cliente.nombre'] = 'required|string|max:100';
                $mensajesCliente['nuevo_cliente.nombre.required'] = 'El nombre del cliente es obligatorio para productos smartphones';
            }

            $validator = Validator::make($request->all(), array_merge($reglasCliente, [
                'fecha' => 'required|date',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => 'required|exists:productos,id_producto',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ]), array_merge($mensajesCliente, [
                'fecha.required' => 'La fecha es obligatoria',
                'fecha.date' => 'La fecha debe tener un formato válido',
                'productos.required' => 'Debe agregar al menos un producto',
                'productos.min' => 'Debe agregar al menos un producto',
                'productos.*.id_producto.required' => 'Debe seleccionar un producto',
                'productos.*.id_producto.exists' => 'El producto seleccionado no existe',
                'productos.*.cantidad.required' => 'Debe especificar la cantidad',
                'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero',
                'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
                'productos.*.precio_unitario.required' => 'Debe especificar el precio unitario',
                'productos.*.precio_unitario.numeric' => 'El precio unitario debe ser un número',
                'productos.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0'
            ]));
        } else {
            // Validar solo los campos básicos cuando no hay nuevo cliente
            $reglas = [
                'fecha' => 'required|date',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => 'required|exists:productos,id_producto',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ];

            $mensajes = [
                'fecha.required' => 'La fecha es obligatoria',
                'fecha.date' => 'La fecha debe tener un formato válido',
                'productos.required' => 'Debe agregar al menos un producto',
                'productos.min' => 'Debe agregar al menos un producto',
                'productos.*.id_producto.required' => 'Debe seleccionar un producto',
                'productos.*.id_producto.exists' => 'El producto seleccionado no existe',
                'productos.*.cantidad.required' => 'Debe especificar la cantidad',
                'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero',
                'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
                'productos.*.precio_unitario.required' => 'Debe especificar el precio unitario',
                'productos.*.precio_unitario.numeric' => 'El precio unitario debe ser un número',
                'productos.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0'
            ];

            // Si hay smartphones, validar que se seleccione un cliente
            if ($clienteEsObligatorio) {
                $reglas['id_cliente'] = 'required|exists:clientes,id_cliente';
                $mensajes['id_cliente.required'] = 'Debe seleccionar un cliente para productos smartphones';
                $mensajes['id_cliente.exists'] = 'El cliente seleccionado no existe';
            }

            $validator = Validator::make($request->all(), $reglas, $mensajes);
        }

        // Validación adicional para verificar stock disponible e IMEI único
        if ($validator->passes()) {
            $imeisEnSolicitud = [];
            foreach ($request->productos as $index => $producto) {
                $stockComprado = DB::table('detalle_compras')
                    ->where('id_producto', $producto['id_producto'])
                    ->sum('cantidad');

                $stockVendido = DB::table('detalle_ventas')
                    ->where('id_producto', $producto['id_producto'])
                    ->sum('cantidad');

                $stockDisponible = $stockComprado - $stockVendido;

                if ($stockDisponible < $producto['cantidad']) {
                    $productoInfo = Producto::with('modelo')->find($producto['id_producto']);
                    $validator->errors()->add(
                        "productos.{$index}.cantidad",
                        "Stock insuficiente para {$productoInfo->modelo->nombre}. Disponible: {$stockDisponible}"
                    );
                }

                // Validaciones IMEI para smartphones/celulares
                $productoObj = Producto::with('categoria')->find($producto['id_producto']);
                $esSmartphone = $productoObj && $productoObj->categoria && in_array(strtolower($productoObj->categoria->nombre), ['smartphones','celulares']);
                if ($esSmartphone) {
                    $imei = isset($producto['descripcion']) ? trim($producto['descripcion']) : '';
                    // IMEI requerido
                    if ($imei === '') {
                        $validator->errors()->add(
                            "productos.{$index}.descripcion",
                            'Debe ingresar el IMEI para el celular seleccionado.'
                        );
                    } else {
                        // Duplicado dentro de la misma solicitud
                        if (in_array($imei, $imeisEnSolicitud, true)) {
                            $validator->errors()->add(
                                "productos.{$index}.descripcion",
                                "El IMEI {$imei} está repetido en esta venta. Cada IMEI debe ser único."
                            );
                        } else {
                            $imeisEnSolicitud[] = $imei;
                        }

                        // Duplicado en la base de datos (ya vendido)
                        $existeImei = DetalleVenta::where('descripcion', $imei)
                            ->whereHas('producto.categoria', function($q) {
                                $q->whereIn(DB::raw('LOWER(nombre)'), ['smartphones','celulares']);
                            })
                            ->exists();

                        if ($existeImei) {
                            $validator->errors()->add(
                                "productos.{$index}.descripcion",
                                "El IMEI {$imei} ya fue vendido y no puede reutilizarse."
                            );
                        }
                    }
                }
            }
        }

        // Validar que el total de la venta sea mayor a 0
        $totalVenta = 0;
        foreach ($request->productos as $producto) {
            $totalVenta += $producto['cantidad'] * $producto['precio_unitario'];
        }

        if ($totalVenta <= 0) {
            $validator->errors()->add(
                'total',
                'El total de la venta debe ser mayor a Bs 0.00'
            );
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Debug: Log del request completo
            \Log::info('=== REQUEST COMPLETO ===');
            \Log::info('Productos recibidos: ' . json_encode($request->productos));
            \Log::info('========================');

            // Calcular el total
            $total = 0;
            foreach ($request->productos as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }

            $productos = collect($request->productos);

            // Obtener cliente (NO crear hasta confirmación)
            $cliente = null;
            if ($esClienteNuevo) {
                // Preparar datos del cliente temporal
                if (!empty($request->nuevo_cliente['nombre'])) {
                    $cliente = (object) [
                        'id_cliente' => 'temp_' . time(),
                        'nombre' => $request->nuevo_cliente['nombre'],
                        'apellidos' => $request->nuevo_cliente['apellidos'] ?? null,
                        'ci' => $request->nuevo_cliente['ci'] ?? null,
                        'telefono' => $request->nuevo_cliente['telefono'] ?? null,
                        'es_temporal' => true
                    ];
                }
            } else {
                $cliente = Cliente::find($request->id_cliente);
            }

            // Preparar datos de la venta para confirmación (sin guardar en BD)
            $ventaData = [
                'id_venta' => 'temp_' . time(), // ID temporal
                'id_cliente' => $cliente ? $cliente->id_cliente : null,
                'id_usuario' => Auth::id(),
                'fecha' => $request->fecha,
                'total' => $total,
                'cliente' => $cliente,
                'nuevo_cliente' => $esClienteNuevo ? $request->nuevo_cliente : null,
                'detalles' => []
            ];

            \Log::info('Debug - ventaData construido:', ['ventaData' => $ventaData]);

            // Preparar detalles con información completa de productos
            foreach ($productos as $item) {
                $productoInfo = Producto::with(['modelo', 'marca', 'categoria'])->find($item['id_producto']);

                // Debug: Verificar si el producto se carga correctamente
                if (!$productoInfo) {
                    \Log::error('Producto no encontrado: ' . $item['id_producto']);
                    continue;
                }

                if (!$productoInfo->categoria) {
                    \Log::error('Categoría no encontrada para producto: ' . $productoInfo->nombre);
                }

                $esSmartphone = $productoInfo && $productoInfo->categoria &&
                               (strtolower($productoInfo->categoria->nombre) === 'smartphones' ||
                                strtolower($productoInfo->categoria->nombre) === 'celulares');

                // Debug: Log para verificar IMEI
                \Log::info('=== DEBUG IMEI ===');
                \Log::info('Producto ID: ' . $item['id_producto']);
                \Log::info('Producto: ' . ($productoInfo ? $productoInfo->nombre : 'No encontrado'));
                \Log::info('Categoría: ' . ($productoInfo && $productoInfo->categoria ? $productoInfo->categoria->nombre : 'No hay categoría'));
                \Log::info('Es smartphone: ' . ($esSmartphone ? 'Sí' : 'No'));
                \Log::info('IMEI del request: "' . ($item['descripcion'] ?? 'No hay descripción') . '"');
                \Log::info('IMEI final: "' . ($esSmartphone ? ($item['descripcion'] ?? null) : null) . '"');
                \Log::info('==================');

                // Para smartphones, usar descripcion para IMEI; para otros productos, usar descripcion normal
                $descripcion = '';
                if ($esSmartphone && isset($item['descripcion']) && !empty($item['descripcion'])) {
                    $descripcion = $item['descripcion']; // IMEI para smartphones
                } elseif (!$esSmartphone && isset($item['descripcion'])) {
                    $descripcion = $item['descripcion']; // Descripción normal para otros productos
                }

                $detalle = [
                    'id_detalle_venta' => 'temp_' . time() . '_' . $item['id_producto'],
                    'id_venta' => $ventaData['id_venta'],
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'total_parcial' => $item['cantidad'] * $item['precio_unitario'],
                    'descripcion' => $descripcion,
                    'producto' => $productoInfo
                ];

                \Log::info('Detalle creado - Descripción: ' . ($descripcion ?? 'null'));

                $ventaData['detalles'][] = $detalle;
            }

            // Guardar datos temporalmente en sesión para confirmación
            session(['venta_temporal' => $ventaData]);

            return redirect()->route('ventas.confirmar-temp');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la venta: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Mostrar vista de confirmación temporal (antes de crear la venta).
     */
    public function confirmarTemp()
    {
        $ventaData = session('venta_temporal');

        if (!$ventaData) {
            return redirect()->route('ventas.create')
                ->withErrors(['error' => 'No hay datos de venta para confirmar']);
        }

        // Debug: Log para verificar los datos que se están enviando
        \Log::info('Debug - Datos de venta temporal en confirmarTemp:', $ventaData);
        if (isset($ventaData['detalles'])) {
            foreach ($ventaData['detalles'] as $index => $detalle) {
                \Log::info("Debug - Detalle $index:", [
                    'descripcion' => $detalle['descripcion'] ?? 'No hay descripción',
                    'producto' => $detalle['producto']->nombre ?? 'No hay producto'
                ]);
            }
        }

        // Limpiar cualquier mensaje de sesión anterior
        session()->forget(['success', 'error', 'info', 'message']);

        return Inertia::render('Ventas/Confirmar', [
            'venta' => $ventaData
        ]);
    }

    /**
     * Finalizar la venta después de confirmación.
     */
    public function finalizarVenta()
    {
        $ventaData = session('venta_temporal');

        if (!$ventaData) {
            return redirect()->route('ventas.create')
                ->withErrors(['error' => 'No hay datos de venta para finalizar']);
        }

        // Validar IMEIs nuevamente justo antes de guardar (evitar condiciones de carrera)
        $imeisEnSolicitud = [];
        foreach ($ventaData['detalles'] as $index => $detalleData) {
            $categoriaNombre = strtolower($detalleData['producto']['categoria']['nombre'] ?? '');
            $esSmartphone = in_array($categoriaNombre, ['smartphones', 'celulares']);
            if ($esSmartphone) {
                $imei = isset($detalleData['descripcion']) ? trim($detalleData['descripcion']) : '';
                if ($imei === '') {
                    return redirect()->route('ventas.create')
                        ->withErrors(["productos.{$index}.descripcion" => 'Debe ingresar el IMEI para el celular seleccionado'])
                        ->withInput();
                }
                if (in_array($imei, $imeisEnSolicitud, true)) {
                    return redirect()->route('ventas.create')
                        ->withErrors(["productos.{$index}.descripcion" => "El IMEI {$imei} está repetido en esta venta. Cada IMEI debe ser único."])
                        ->withInput();
                }
                $imeisEnSolicitud[] = $imei;

                $existeImei = DetalleVenta::where('descripcion', $imei)
                    ->whereHas('producto.categoria', function($q) {
                        $q->whereIn(DB::raw('LOWER(nombre)'), ['smartphones','celulares']);
                    })
                    ->exists();
                if ($existeImei) {
                    return redirect()->route('ventas.create')
                        ->withErrors(["productos.{$index}.descripcion" => "El IMEI {$imei} ya fue vendido y no puede reutilizarse."])
                        ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            // Crear cliente si es nuevo
            $idCliente = $ventaData['id_cliente'];
            if (isset($ventaData['nuevo_cliente']) && $ventaData['nuevo_cliente']) {
                $cliente = Cliente::create([
                    'nombre' => $ventaData['nuevo_cliente']['nombre'],
                    'apellidos' => $ventaData['nuevo_cliente']['apellidos'] ?? null,
                    'ci' => $ventaData['nuevo_cliente']['ci'] ?? null,
                    'telefono' => $ventaData['nuevo_cliente']['telefono'] ?? null,
                    'correo_electronico' => null
                ]);
                $idCliente = $cliente->id_cliente;
            }

            // Crear la venta real en la base de datos
            $venta = Venta::create([
                'id_cliente' => $idCliente,
                'id_usuario' => $ventaData['id_usuario'],
                'fecha' => $ventaData['fecha']
            ]);

            // Crear los detalles de la venta
            foreach ($ventaData['detalles'] as $detalleData) {
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $detalleData['id_producto'],
                    'cantidad' => $detalleData['cantidad'],
                    'precio_unitario' => $detalleData['precio_unitario'],
                    'descripcion' => $detalleData['descripcion']
                ]);
            }

            DB::commit();

            // Limpiar datos temporales
            session()->forget('venta_temporal');

            return redirect()->route('ventas.index')
                ->with('success', 'Venta creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('ventas.create')
                ->withErrors(['error' => 'Error al finalizar la venta: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancelar venta temporal.
     */
    public function cancelarVenta()
    {
        // Limpiar datos temporales
        session()->forget('venta_temporal');

        return redirect()->route('ventas.index')
            ->with('info', 'Venta cancelada');
    }

    /**
     * Guardar datos de edición en sesión.
     */
    public function guardarEdicion(Request $request)
    {
        // Guardar los datos de la venta temporal para edición
        session(['venta_edicion' => $request->venta_temporal]);

        // Redirigir directamente a la página de creación
        return redirect()->route('ventas.create');
    }

    /**
     * Limpiar datos de edición de la sesión.
     */
    public function limpiarEdicion()
    {
        session()->forget('venta_edicion');

        return response()->json(['success' => true]);
    }

    /**
     * Mostrar vista de confirmación de venta.
     */
    public function confirmar(string $id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->findOrFail($id);

        // Asegurar que el campo imei se cargue
        $venta->load('detalles');

        // Calcular totales para cada detalle
        $venta->detalles->transform(function ($detalle) {
            $detalle->total_parcial = $detalle->cantidad * $detalle->precio_unitario;
            return $detalle;
        });

        return Inertia::render('Ventas/Confirmar', [
            'venta' => $venta
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->findOrFail($id);

        // Calcular totales para cada detalle
        $venta->detalles->transform(function ($detalle) {
            $detalle->total_parcial = $detalle->cantidad * $detalle->precio_unitario;
            return $detalle;
        });

        // El total se calcula dinámicamente mediante el accessor getTotalAttribute()

        return Inertia::render('Ventas/Show', [
            'venta' => $venta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->findOrFail($id);

        $clientes = Cliente::orderBy('nombre')->get();

        // Obtener productos con stock calculado
        $productos = Producto::with(['categoria', 'marca', 'modelo'])->get()->map(function ($producto) {
            $producto->stock_disponible = $producto->stock_disponible;
            $producto->estado_disponible = $producto->estado_disponible;
            return $producto;
        });

        return Inertia::render('Ventas/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
            'productos' => $productos
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $venta = Venta::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'fecha' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'id_cliente.required' => 'Debe seleccionar un cliente',
            'id_cliente.exists' => 'El cliente seleccionado no existe',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe tener un formato válido',
            'productos.required' => 'Debe agregar al menos un producto',
            'productos.min' => 'Debe agregar al menos un producto',
            'productos.*.id_producto.required' => 'Debe seleccionar un producto',
            'productos.*.id_producto.exists' => 'El producto seleccionado no existe',
            'productos.*.cantidad.required' => 'Debe especificar la cantidad',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'productos.*.precio_unitario.required' => 'Debe especificar el precio unitario',
            'productos.*.precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'productos.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0',
        ]);

        // Validar que el total de la venta sea mayor a 0
        $totalVenta = 0;
        foreach ($request->productos as $producto) {
            $totalVenta += $producto['cantidad'] * $producto['precio_unitario'];
        }

        if ($totalVenta <= 0) {
            $validator->errors()->add(
                'total',
                'El total de la venta debe ser mayor a Bs 0.00'
            );
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();



            // Eliminar detalles anteriores
            $venta->detalles()->delete();

            // Calcular el total antes de actualizar la venta
            $total = 0;
            foreach ($request->productos as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }

            // Actualizar información básica de la venta
            $venta->update([
                'id_cliente' => $request->id_cliente,
                'fecha' => $request->fecha
            ]);

            // Verificar stock disponible para nuevos productos
            foreach ($request->productos as $producto) {
                // Calcular stock disponible: compras - ventas (excluyendo esta venta)
                $stockComprado = DB::table('detalle_compras')
                    ->where('id_producto', $producto['id_producto'])
                    ->sum('cantidad');

                $stockVendido = DB::table('detalle_ventas')
                    ->where('id_producto', $producto['id_producto'])
                    ->where('id_venta', '!=', $venta->id_venta)
                    ->sum('cantidad');

                $stockDisponible = $stockComprado - $stockVendido;

                if ($stockDisponible < $producto['cantidad']) {
                    $prod = Producto::find($producto['id_producto']);
                    return back()->withErrors(['error' => "Stock insuficiente para {$prod->nombre}. Disponible: {$stockDisponible}"])->withInput();
                }
            }

            // Crear nuevos detalles
            foreach ($request->productos as $producto) {
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descripcion' => $producto['descripcion'] ?? ''
                ]);
            }

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la venta: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $venta = Venta::with('detalles.producto')->findOrFail($id);

            DB::beginTransaction();

            // Marcar como eliminada en lugar de eliminar físicamente
            $venta->update([
                'estado' => true,
                'fecha_eliminacion' => now()
            ]);

            // El stock se maneja dinámicamente a través de compras y ventas
            // No necesitamos hacer nada aquí ya que el stock se calcula automáticamente

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada exitosamente. El stock de los productos ha sido devuelto.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la venta: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar ventas eliminadas
     */
    public function eliminadas(Request $request)
    {
        $search = $request->get('search');

        $query = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->where('estado', true);

        // Si hay búsqueda, filtrar las ventas
        if ($search) {
            $query->where(function($q) use ($search) {
                // Buscar por nombre de cliente
                $q->whereHas('cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', "%{$search}%");
                })
                // Buscar por nombre de producto en detalles
                ->orWhereHas('detalles.producto', function($productoQuery) use ($search) {
                    $productoQuery->whereHas('modelo', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
                })
                // Buscar por descripción en detalles
                ->orWhereHas('detalles', function($detalleQuery) use ($search) {
                    $detalleQuery->where('descripcion', 'like', "%{$search}%");
                });
            });
        }

        $ventas = $query->orderBy('fecha_eliminacion', 'desc')->get();

        // Calcular totales y ganancias para cada venta
        $ventas->transform(function ($venta) {
            // El total se calcula dinámicamente mediante el accessor getTotalAttribute()
            return $venta;
        });

        return Inertia::render('Ventas/Eliminadas', [
            'ventas' => $ventas,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function recibo($id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.modelo', 'detalles.producto.marca', 'detalles.producto.categoria'])
            ->findOrFail($id);

        // Generar descripción dinámica según la categoría
        foreach ($venta->detalles as $detalle) {
            if ($detalle->producto->categoria &&
                $detalle->producto->categoria->nombre === 'Smartphones') {
                // Solo para Smartphones mostrar IMEI
                $detalle->descripcion_dinamica = 'IMEI: ' . ($detalle->descripcion ?: 'No especificado');
            } else {
                // Para todas las demás categorías mostrar Desc: o nada si no hay descripción
                if ($detalle->descripcion) {
                    $detalle->descripcion_dinamica = 'Desc: ' . $detalle->descripcion;
                } else {
                    $detalle->descripcion_dinamica = null; // No mostrar nada si no hay descripción
                }
            }
        }

        // Obtener información del usuario
        $usuario = auth()->user();

        // Generar PDF
        $html = view('reportes.recibo-venta', [
            'venta' => $venta,
            'usuario' => $usuario,
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Convertir PDF a base64 para enviar como JSON
        $pdfContent = $pdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json([
            'success' => true,
            'pdf' => $base64Pdf,
            'filename' => 'recibo_venta_' . $venta->id_venta . '_' . now()->format('Y-m-d_H-i-s') . '.pdf'
        ]);
    }
}
