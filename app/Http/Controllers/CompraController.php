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

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'usuario']);

        // Filtros
        if ($request->filled('search')) {
            $query->whereHas('proveedor', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('proveedor')) {
            $query->where('id_proveedor', $request->proveedor);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $compras = $query->orderBy('fecha', 'desc')->paginate(10);

        // Obtener datos para filtros
        $proveedores = Proveedor::orderBy('nombre')->get();

        return Inertia::render('compras/index', [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'filters' => $request->only(['search', 'proveedor', 'fecha_desde', 'fecha_hasta']) ?: []
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

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

            // Calcular total
            $total = collect($request->productos)->sum(function ($producto) {
                return $producto['cantidad'] * $producto['precio_unitario'];
            });

            // Crear la compra
            $compra = Compra::create([
                'id_proveedor' => $request->id_proveedor,
                'id_usuario' => Auth::id(),
                'fecha' => $request->fecha,
                'total' => $total
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

            // Calcular total
            $total = collect($request->productos)->sum(function ($producto) {
                return $producto['cantidad'] * $producto['precio_unitario'];
            });

            // Actualizar la compra
            $compra->update([
                'id_proveedor' => $request->id_proveedor,
                'fecha' => $request->fecha,
                'total' => $total
            ]);

            // Eliminar detalles existentes
            $compra->detalles()->delete();

            // Crear los nuevos detalles
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

            // Eliminar detalles (se eliminan automáticamente por la cascada)
            $compra->delete();

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la compra: ' . $e->getMessage()]);
        }
    }
}
