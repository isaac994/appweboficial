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
        $query = Venta::with(['cliente', 'detalles.producto']);

        // Filtros
        if ($request->filled('search')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%');
            });
        }

        $ventas = $query->orderBy('fecha', 'desc')->paginate(15);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca'])->get();

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
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'fecha' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0'
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
            'productos.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $total = 0;
            $productos = collect($request->productos);

            // Verificar stock disponible
            foreach ($productos as $item) {
                $producto = Producto::find($item['id_producto']);

                // Calcular stock disponible: compras - ventas
                $stockComprado = DB::table('detalle_compras')
                    ->where('id_producto', $item['id_producto'])
                    ->sum('cantidad');

                $stockVendido = DB::table('detalle_ventas')
                    ->where('id_producto', $item['id_producto'])
                    ->sum('cantidad');

                $stockDisponible = $stockComprado - $stockVendido;

                if ($stockDisponible < $item['cantidad']) {
                    return back()->withErrors(['error' => "Stock insuficiente para {$producto->nombre}. Disponible: {$stockDisponible}"])->withInput();
                }
            }

            // Crear la venta
            $venta = Venta::create([
                'id_cliente' => $request->id_cliente,
                'id_usuario' => Auth::id(),
                'fecha' => $request->fecha,
                'total' => 0
            ]);

            // Crear los detalles de la venta
            foreach ($productos as $item) {
                $producto = Producto::find($item['id_producto']);
                $precioUnitario = $producto->precio_venta;
                $subtotal = $item['cantidad'] * $precioUnitario;

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'total_parcial' => $subtotal
                ]);



                $total += $subtotal;
            }

            // Actualizar el total de la venta
            $venta->update(['total' => $total]);

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
        $productos = Producto::with(['categoria', 'marca'])->get();

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
                    'total_parcial' => $subtotal
                ]);
            }

            // Actualizar el total de la venta
            $venta->update(['total' => $total]);

            DB::commit();

            return redirect()->route('ventas.show', $venta->id_venta)
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
            $venta = Venta::with('detalles')->findOrFail($id);

            DB::beginTransaction();



            // Eliminar la venta (los detalles se eliminan por cascade)
            $venta->delete();

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la venta: ' . $e->getMessage()]);
        }
    }
}
