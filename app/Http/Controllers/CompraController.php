<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class CompraController extends Controller
{
    /**
     * Mostrar vista para seleccionar productos para compra
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

        // Mostrar todos los productos (no filtrar por stock para compras)
        return Inertia::render('compras/seleccionarProductos', [
            'productos' => $productos
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'usuario', 'detalles'])
            ->where('estado', false);

        // Filtro multicampo de búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            \Log::info('Búsqueda en compras:', ['term' => $searchTerm]);

            $query->where(function ($q) use ($searchTerm) {
                // Buscar por nombre del proveedor
                $q->whereHas('proveedor', function ($subQ) use ($searchTerm) {
                    $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                });

                // Si el término de búsqueda es numérico, también buscar por ID de compra
                if (is_numeric($searchTerm)) {
                    $q->orWhere('id_compra', 'like', '%' . $searchTerm . '%');
                }
            });
        }


        $compras = $query->orderBy('id_compra', 'desc')->paginate(10);

        // Calcular el total de cada compra manualmente
        $compras->getCollection()->transform(function ($compra) {
            $compra->total = $compra->detalles->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
            return $compra;
        });

        return Inertia::render('compras/index', [
            'compras' => $compras,
            'filters' => $request->only(['search']) ?: []
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca', 'modelo'])->orderBy('id_producto', 'desc')->get();

        return Inertia::render('compras/create', [
            'proveedores' => $proveedores,
            'productos' => $productos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Determinar si es un proveedor nuevo o existente
        $esProveedorNuevo = $request->has('nuevo_proveedor') &&
                           $request->nuevo_proveedor &&
                           isset($request->nuevo_proveedor['nombre']) &&
                           isset($request->nuevo_proveedor['ci_nit']) &&
                           isset($request->nuevo_proveedor['telefono']);

        if ($esProveedorNuevo) {
            // Validar datos del nuevo proveedor
            $validator = Validator::make($request->all(), [
                'nuevo_proveedor.nombre' => 'required|string|max:100',
                'nuevo_proveedor.ci_nit' => 'required|string|max:20|unique:proveedores,ci_nit',
                'nuevo_proveedor.telefono' => 'required|string|max:25',
                'fecha' => 'required|date',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => 'required|exists:productos,id_producto',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ], [
                'nuevo_proveedor.nombre.required' => 'El nombre del proveedor es obligatorio',
                'nuevo_proveedor.ci_nit.required' => 'El CI/NIT del proveedor es obligatorio',
                'nuevo_proveedor.ci_nit.unique' => 'Ya existe un proveedor con este CI/NIT',
                'nuevo_proveedor.telefono.required' => 'El teléfono del proveedor es obligatorio',
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
            ]);
        } else {
            // Validar solo los campos básicos cuando no hay nuevo proveedor
            $validator = Validator::make($request->all(), [
                'id_proveedor' => 'required|exists:proveedores,id_proveedor',
                'fecha' => 'required|date',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => 'required|exists:productos,id_producto',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ], [
                'id_proveedor.required' => 'Debe seleccionar un proveedor',
                'id_proveedor.exists' => 'El proveedor seleccionado no existe',
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
            ]);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Crear proveedor si es nuevo
            $idProveedor = $request->id_proveedor;
            if ($esProveedorNuevo) {
                $proveedor = Proveedor::create([
                    'nombre' => $request->nuevo_proveedor['nombre'],
                    'ci_nit' => $request->nuevo_proveedor['ci_nit'],
                    'telefono' => $request->nuevo_proveedor['telefono']
                ]);
                $idProveedor = $proveedor->id_proveedor;
            }

            // Crear la compra
            $compra = Compra::create([
                'id_proveedor' => $idProveedor,
                'id_usuario' => Auth::id(),
                'fecha' => $request->fecha
            ]);

            // Crear los detalles
            foreach ($request->productos as $producto) {
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'total_parcial' => $producto['cantidad'] * $producto['precio_unitario']
                ]);
            }

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la compra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Preparar datos de compra temporal para confirmación
     */
    public function confirmar(Request $request)
    {
        // Calcular el total
        $total = 0;
        foreach ($request->productos as $producto) {
            $total += $producto['cantidad'] * $producto['precio_unitario'];
        }

        $productos = collect($request->productos);

        // Obtener proveedor
        $proveedor = null;
        if ($request->has('nuevo_proveedor') && $request->nuevo_proveedor) {
            $proveedor = null; // Se creará después
        } else {
            $proveedor = Proveedor::find($request->id_proveedor);
        }

        // Preparar datos de la compra para confirmación
        $compraData = [
            'id_compra' => 'temp_' . time(),
            'fecha' => $request->fecha,
            'total' => $total,
            'proveedor' => $proveedor,
            'nuevo_proveedor' => $request->nuevo_proveedor ?? null,
            'detalles' => []
        ];

        // Preparar detalles con información completa de productos
        foreach ($request->productos as $item) {
            $productoInfo = Producto::with(['categoria', 'marca', 'modelo'])->find($item['id_producto']);

            $detalle = [
                'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'total_parcial' => $item['cantidad'] * $item['precio_unitario'],
                'descripcion' => $item['descripcion'] ?? '',
                'producto' => $productoInfo
            ];

            $compraData['detalles'][] = $detalle;
        }

        // Guardar datos temporalmente en sesión
        session(['compra_temporal' => $compraData]);

        return redirect()->route('compras.confirmar-temp');
    }

    /**
     * Mostrar vista de confirmación temporal
     */
    public function confirmarTemp()
    {
        $compraData = session('compra_temporal');

        if (!$compraData) {
            return redirect()->route('compras.create')
                ->withErrors(['error' => 'No hay datos de compra para confirmar']);
        }

        return Inertia::render('compras/Confirmar', [
            'compra' => $compraData
        ]);
    }

    /**
     * Finalizar la compra (crear en BD)
     */
    public function finalizar()
    {
        $compraData = session('compra_temporal');

        if (!$compraData) {
            return redirect()->route('compras.create')
                ->withErrors(['error' => 'No hay datos de compra para finalizar']);
        }

        try {
            DB::beginTransaction();

            // Crear proveedor si es nuevo
            $idProveedor = null;
            if ($compraData['nuevo_proveedor']) {
                $proveedor = Proveedor::create([
                    'nombre' => $compraData['nuevo_proveedor']['nombre'],
                    'ci_nit' => $compraData['nuevo_proveedor']['ci_nit'],
                    'telefono' => $compraData['nuevo_proveedor']['telefono']
                ]);
                $idProveedor = $proveedor->id_proveedor;
            } else {
                $idProveedor = $compraData['proveedor']->id_proveedor;
            }

            // Crear la compra real en la base de datos
            $compra = Compra::create([
                'id_proveedor' => $idProveedor,
                'id_usuario' => Auth::id(),
                'fecha' => $compraData['fecha']
            ]);

            // Crear los detalles de la compra
            foreach ($compraData['detalles'] as $detalleData) {
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $detalleData['id_producto'],
                    'cantidad' => $detalleData['cantidad'],
                    'precio_unitario' => $detalleData['precio_unitario'],
                    'descripcion' => $detalleData['descripcion'] ?? ''
                ]);
            }

            DB::commit();

            // Limpiar datos temporales
            session()->forget('compra_temporal');

            return redirect()->route('compras.index')
                ->with('success', 'Compra creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al finalizar la compra: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca']);

        return Inertia::render('compras/show', [
            'compra' => $compra
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        $compra->load(['proveedor', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca']);

        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca', 'modelo'])->orderBy('id_producto', 'desc')->get();

        return Inertia::render('compras/edit', [
            'compra' => $compra,
            'proveedores' => $proveedores,
            'productos' => $productos
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        $validator = Validator::make($request->all(), [
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'fecha' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'id_proveedor.required' => 'El proveedor es obligatorio',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe tener un formato válido',
            'productos.required' => 'Debe agregar al menos un producto',
            'productos.min' => 'Debe agregar al menos un producto',
            'productos.*.id_producto.required' => 'El producto es obligatorio',
            'productos.*.id_producto.exists' => 'El producto seleccionado no existe',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'productos.*.precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'productos.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();



            // Actualizar la compra
            $compra->update([
                'id_proveedor' => $request->id_proveedor,
                'fecha' => $request->fecha
            ]);

            // Eliminar detalles existentes
            $compra->detalles()->delete();

            // Crear los nuevos detalles
            foreach ($request->productos as $producto) {
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario']
                ]);
            }

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la compra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {
        try {
            DB::beginTransaction();

            // No permitir eliminar compras que ya fueron eliminadas
            if ($compra->estado === true) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Esta compra ya está eliminada.']);
            }

            // Cargar los detalles de la compra con los productos
            $compra->load('detalles.producto');

            // Verificar si algún producto de esta compra ya fue vendido
            // IMPORTANTE: Solo considerar ventas activas (no eliminadas)
            $productosConVentas = [];
            foreach ($compra->detalles as $detalle) {
                // Obtener total de ventas activas (no eliminadas) para este producto
                $totalVentasActivas = DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $detalle->id_producto)
                    ->where('ventas.estado', false) // Solo ventas activas
                    ->sum('detalle_ventas.cantidad');

                // Obtener total de compras activas (no eliminadas) para este producto
                $totalComprasActivas = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $detalle->id_producto)
                    ->where('compras.estado', false) // Solo compras activas
                    ->sum('detalle_compras.cantidad');

                // Stock actual basado solo en compras y ventas activas
                $stockActual = $totalComprasActivas - $totalVentasActivas;

                // Calcular stock SIN esta compra específica
                $stockSinEstaCompra = ($totalComprasActivas - $detalle->cantidad) - $totalVentasActivas;

                // Si hay ventas activas Y el stock sin esta compra sería negativo o insuficiente,
                // significa que esta compra tiene unidades vendidas
                $tieneVentasDeEstaCompra = false;
                $cantidadVendidaDeEstaCompra = 0;

                if ($totalVentasActivas > 0) {
                    // Si el stock sin esta compra es menor que 0, definitivamente esta compra tiene ventas
                    if ($stockSinEstaCompra < 0) {
                        $tieneVentasDeEstaCompra = true;
                        $cantidadVendidaDeEstaCompra = min(abs($stockSinEstaCompra), $detalle->cantidad);
                    } elseif ($stockActual <= 0) {
                        // Si el stock total es 0 o negativo, y hay ventas, no podemos eliminar
                        // porque significa que todas las compras tienen ventas asociadas
                        $tieneVentasDeEstaCompra = true;
                        $cantidadVendidaDeEstaCompra = min($detalle->cantidad, $totalVentasActivas);
                    }
                }

                // Si esta compra tiene unidades vendidas, bloquear eliminación
                if ($tieneVentasDeEstaCompra) {
                    $producto = $detalle->producto;

                    // Calcular mejor el mensaje de error
                    $cantidadVendida = $cantidadVendidaDeEstaCompra > 0
                        ? $cantidadVendidaDeEstaCompra
                        : ($stockActual <= 0 ? $detalle->cantidad : 0);

                    $productosConVentas[] = [
                        'producto' => ($producto->modelo->nombre ?? $producto->descripcion ?? 'Sin nombre') .
                                     ($producto->descripcion ? ' (' . $producto->descripcion . ')' : ''),
                        'cantidad_comprada' => $detalle->cantidad,
                        'cantidad_vendida' => $cantidadVendida,
                        'stock_actual' => $stockActual,
                        'total_ventas_producto' => $totalVentasActivas
                    ];
                }
            }

            // Si hay productos que ya fueron vendidos, no permitir la eliminación
            if (!empty($productosConVentas)) {
                DB::rollBack();

                $mensaje = "No se puede eliminar esta compra porque algunos productos ya fueron vendidos:\n\n";
                foreach ($productosConVentas as $item) {
                    $mensaje .= "• {$item['producto']}: ";
                    $mensaje .= "Comprado {$item['cantidad_comprada']}, ";
                    if ($item['cantidad_vendida'] > 0) {
                        $mensaje .= "Vendido {$item['cantidad_vendida']}, ";
                    }
                    $mensaje .= "Stock actual: {$item['stock_actual']}\n";
                }
                $mensaje .= "\nPara eliminar esta compra, primero debe eliminar las ventas relacionadas.";

                return back()->withErrors(['error' => $mensaje]);
            }

            // Marcar como eliminada en lugar de eliminar físicamente
            $compra->update([
                'estado' => true,
                'fecha_eliminacion' => now()
            ]);

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra eliminada exitosamente. El stock de los productos ha sido actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la compra: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate PDF receipt for the specified purchase.
     */
    public function recibo(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto.modelo', 'detalles.producto.categoria', 'detalles.producto.marca']);

        // Generar descripción dinámica para todos los productos
        foreach ($compra->detalles as $detalle) {
            if ($detalle->descripcion) {
                $detalle->descripcion_dinamica = 'Desc: ' . $detalle->descripcion;
            } else {
                $detalle->descripcion_dinamica = null; // No mostrar nada si no hay descripción
            }
        }

        // Calcular el total de la compra
        $totalCompra = $compra->detalles->sum(function ($detalle) {
            return $detalle->cantidad * $detalle->precio_unitario;
        });

        // Obtener información del usuario
        $usuario = auth()->user();

        $data = [
            'compra' => $compra,
            'totalCompra' => $totalCompra,
            'usuario' => $usuario,
            'fecha' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('reportes.recibo-compra', $data);

        // Convertir a base64 para enviar como JSON
        $pdfContent = $pdf->output();
        $pdfBase64 = base64_encode($pdfContent);

        return response()->json([
            'success' => true,
            'pdf' => $pdfBase64,
            'filename' => "recibo-compra-{$compra->id_compra}.pdf"
        ]);
    }

    /**
     * Mostrar compras eliminadas
     */
    public function eliminadas(Request $request)
    {
        $search = $request->get('search');

        $query = Compra::with(['proveedor', 'usuario', 'detalles'])
            ->where('estado', true)
            ->orderBy('fecha_eliminacion', 'desc');

        // Si hay búsqueda, filtrar las compras
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('proveedor', function($proveedorQuery) use ($search) {
                    $proveedorQuery->where('nombre', 'like', '%' . $search . '%');
                })
                ->orWhere('id_compra', 'like', '%' . $search . '%');
            });
        }

        $compras = $query->get();

        return Inertia::render('compras/eliminadas', [
            'compras' => $compras,
            'search' => $search
        ]);
    }

    /**
     * Restaurar una compra eliminada (revertir eliminación)
     */
    public function restaurar($id)
    {
        try {
            DB::beginTransaction();

            $compra = Compra::where('id_compra', $id)
                ->where('estado', true)
                ->firstOrFail();

            // Verificar si restaurar esta compra causará inconsistencias
            // (es decir, si ya hay ventas que podrían estar relacionadas)
            $compra->load('detalles.producto');

            $tieneProblemas = false;
            $mensajeProblemas = [];

            foreach ($compra->detalles as $detalle) {
                // Obtener ventas activas para este producto
                $ventasActivas = DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $detalle->id_producto)
                    ->where('ventas.estado', false)
                    ->sum('detalle_ventas.cantidad');

                // Obtener compras activas (sin incluir esta que vamos a restaurar)
                $comprasActivas = DB::table('detalle_compras')
                    ->join('compras', 'detalle_compras.id_compra', '=', 'compras.id_compra')
                    ->where('detalle_compras.id_producto', $detalle->id_producto)
                    ->where('compras.estado', false)
                    ->sum('detalle_compras.cantidad');

                // Stock actual sin esta compra
                $stockSinEstaCompra = $comprasActivas - $ventasActivas;

                // Si hay ventas y el stock sin esta compra sería negativo o insuficiente,
                // restaurarla causaría inconsistencia
                if ($ventasActivas > 0 && $stockSinEstaCompra < 0) {
                    $tieneProblemas = true;
                    $producto = $detalle->producto;
                    $mensajeProblemas[] = ($producto->modelo->nombre ?? $producto->descripcion ?? 'Sin nombre') .
                                         ": Stock actual sin esta compra sería {$stockSinEstaCompra}, pero hay {$ventasActivas} unidades vendidas";
                }
            }

            if ($tieneProblemas) {
                DB::rollBack();
                $mensaje = "No se puede restaurar esta compra porque causaría inconsistencias:\n\n" .
                          implode("\n", array_map(fn($m) => "• {$m}", $mensajeProblemas));
                return back()->withErrors(['error' => $mensaje]);
            }

            // Restaurar la compra
            $compra->update([
                'estado' => false,
                'fecha_eliminacion' => null
            ]);

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra restaurada exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Compra no encontrada o no está eliminada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al restaurar la compra: ' . $e->getMessage()]);
        }
    }
}
