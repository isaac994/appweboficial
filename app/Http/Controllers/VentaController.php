<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetalleVenta;
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

        $query = Venta::with(['cliente', 'detalles.producto', 'detalles.producto.categoria', 'detalles.producto.marca']);

        // Si hay búsqueda, filtrar las ventas
        if ($search) {
            $query->where(function($q) use ($search) {
                // Buscar por nombre de cliente
                $q->whereHas('cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', "%{$search}%");
                })
                // Buscar por nombre de producto en detalles
                ->orWhereHas('detalles.producto', function($productoQuery) use ($search) {
                    $productoQuery->where('nombre', 'like', "%{$search}%");
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

        // Calcular totales
        $totalVentas = $ventas->sum('total');
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
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();

                // Obtener solo productos con stock disponible usando la consulta SQL optimizada
        $productos = DB::table('productos as p')
            ->select([
                'p.id_producto',
                'p.nombre',
                'p.descripcion',
                'p.precio_venta',
                'p.img_url',
                'p.id_categoria',
                'p.id_marca',
                'p.created_at',
                'p.updated_at'
            ])
            ->selectRaw('
                (COALESCE((SELECT SUM(cantidad) FROM detalle_compras WHERE id_producto = p.id_producto), 0) -
                 COALESCE((SELECT SUM(cantidad) FROM detalle_ventas WHERE id_producto = p.id_producto), 0)) as stock_disponible
            ')
            ->havingRaw('stock_disponible > 0')
            ->orderBy('p.nombre')
            ->get();

        // Cargar las relaciones de categoría y marca
        $productos = $productos->map(function ($producto) {
            $productoObj = new Producto();
            foreach ($producto as $key => $value) {
                $productoObj->$key = $value;
            }

            // Cargar categoría
            if ($producto->id_categoria) {
                $productoObj->categoria = DB::table('categorias')
                    ->where('id_categoria', $producto->id_categoria)
                    ->first();
            }

            // Cargar marca
            if ($producto->id_marca) {
                $productoObj->marca = DB::table('marcas')
                    ->where('id_marca', $producto->id_marca)
                    ->first();
            }

            $productoObj->stock_disponible = $producto->stock_disponible;
            $productoObj->estado_disponible = 'disponible';

            return $productoObj;
        });

        return Inertia::render('Ventas/Create', [
            'clientes' => $clientes,
            'productos' => $productos
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
        $esClienteNuevo = $request->has('nuevo_cliente') &&
                         $request->nuevo_cliente &&
                         isset($request->nuevo_cliente['nombre']) &&
                         !empty($request->nuevo_cliente['nombre']);

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

        // Validación adicional para verificar stock disponible
        if ($validator->passes()) {
            foreach ($request->productos as $index => $producto) {
                $stockComprado = DB::table('detalle_compras')
                    ->where('id_producto', $producto['id_producto'])
                    ->sum('cantidad');

                $stockVendido = DB::table('detalle_ventas')
                    ->where('id_producto', $producto['id_producto'])
                    ->sum('cantidad');

                $stockDisponible = $stockComprado - $stockVendido;

                if ($stockDisponible < $producto['cantidad']) {
                    $productoInfo = Producto::find($producto['id_producto']);
                    $validator->errors()->add(
                        "productos.{$index}.cantidad",
                        "Stock insuficiente para {$productoInfo->nombre}. Disponible: {$stockDisponible}"
                    );
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
            DB::beginTransaction();

            $total = 0;
            $productos = collect($request->productos);



            // Crear cliente si es nuevo
            $idCliente = null;
            if ($esClienteNuevo) {
                // Solo crear cliente si hay datos válidos
                if (!empty($request->nuevo_cliente['nombre'])) {
                    $cliente = Cliente::create([
                        'nombre' => $request->nuevo_cliente['nombre'],
                        'apellidos' => $request->nuevo_cliente['apellidos'] ?? null,
                        'ci' => $request->nuevo_cliente['ci'] ?? null,
                        'telefono' => $request->nuevo_cliente['telefono'] ?? null
                    ]);
                    $idCliente = $cliente->id_cliente;
                }
            } else {
                $idCliente = $request->id_cliente;
            }

            // Crear la venta
            $venta = Venta::create([
                'id_cliente' => $idCliente,
                'id_usuario' => Auth::id(),
                'fecha' => $request->fecha,
                'total' => 0
            ]);

            // Crear los detalles de la venta
            foreach ($productos as $item) {
                // Usar el precio modificado por el usuario, no el precio por defecto del producto
                $precioUnitario = $item['precio_unitario'];
                $subtotal = $item['cantidad'] * $precioUnitario;

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descripcion' => $item['descripcion'] ?? ''
                ]);

                $total += $subtotal;
            }

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la venta: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->findOrFail($id);

        // Calcular totales para cada detalle
        $venta->detalles->transform(function ($detalle) {
            $detalle->total_parcial = $detalle->cantidad * $detalle->precio_unitario;
            return $detalle;
        });

        // Calcular total de la venta
        $venta->total = $venta->detalles->sum('total_parcial');

        return Inertia::render('Ventas/Show', [
            'venta' => $venta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.categoria', 'detalles.producto.marca'])
            ->findOrFail($id);

        $clientes = Cliente::orderBy('nombre')->get();

        // Obtener productos con stock calculado
        $productos = Producto::with(['categoria', 'marca'])->get()->map(function ($producto) {
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

            // Actualizar información básica de la venta
            $venta->update([
                'id_cliente' => $request->id_cliente,
                'fecha' => $request->fecha
            ]);

            $total = 0;

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
                $subtotal = $producto['cantidad'] * $producto['precio_unitario'];
                $total += $subtotal;

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
            $venta = Venta::findOrFail($id);

            DB::beginTransaction();

            // Eliminar la venta (los detalles se eliminan automáticamente por la cascada)
            // Al eliminar la venta, el stock se devuelve automáticamente ya que
            // se calcula dinámicamente basado en las compras y ventas
            $venta->delete();

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada exitosamente. El stock de los productos ha sido devuelto.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la venta: ' . $e->getMessage()]);
        }
    }

    public function recibo($id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto.categoria'])
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
