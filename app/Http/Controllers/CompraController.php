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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'usuario', 'detalles']);

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
        $productos = Producto::with(['categoria', 'marca'])->orderBy('nombre')->get();

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
                           isset($request->nuevo_proveedor['telefono']) &&
                           !empty($request->nuevo_proveedor['nombre']) &&
                           !empty($request->nuevo_proveedor['ci_nit']) &&
                           !empty($request->nuevo_proveedor['telefono']);

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
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
                $compra->load(['proveedor', 'usuario', 'detalles.producto']);

        return Inertia::render('compras/show', [
            'compra' => $compra
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        $compra->load(['proveedor', 'detalles.producto']);

        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

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

            // Cargar los detalles de la compra con los productos
            $compra->load('detalles.producto');

            // Reducir el stock de cada producto antes de eliminar la compra
            foreach ($compra->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    // Reducir el stock disponible
                    $producto->stock_disponible -= $detalle->cantidad;

                    // Asegurar que el stock no sea negativo
                    if ($producto->stock_disponible < 0) {
                        $producto->stock_disponible = 0;
                    }

                    $producto->save();
                }
            }

            // Eliminar detalles (se eliminan automáticamente por la cascada)
            $compra->delete();

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
        $compra->load(['proveedor', 'usuario', 'detalles.producto.categoria']);

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
}
