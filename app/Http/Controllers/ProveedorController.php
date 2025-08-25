<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Proveedor::withCount('productos');

        // Filtros
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('correo_electronico', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        $proveedores = $query->orderBy('nombre')->paginate(10);

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Proveedores/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:25',
            'direccion' => 'nullable|string|max:255',
            'correo_electronico' => 'nullable|email|max:150|unique:proveedores,correo_electronico'
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 150 caracteres',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres',
            'correo_electronico.email' => 'El correo electrónico debe tener un formato válido',
            'correo_electronico.unique' => 'Este correo electrónico ya está registrado'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $proveedor = Proveedor::create($request->all());

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor creado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el proveedor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $productos = $proveedor->productos()->select('id_producto', 'nombre', 'precio_venta')->orderBy('nombre')->get();

        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
            'productos' => $productos
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:25',
            'direccion' => 'nullable|string|max:255',
            'correo_electronico' => 'nullable|email|max:150|unique:proveedores,correo_electronico,' . $id . ',id_proveedor'
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 150 caracteres',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres',
            'correo_electronico.email' => 'El correo electrónico debe tener un formato válido',
            'correo_electronico.unique' => 'Este correo electrónico ya está registrado'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $proveedor->update($request->all());

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el proveedor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);

            // Verificar si tiene productos asociados
            if ($proveedor->productos()->count() > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar el proveedor porque tiene productos asociados']);
            }

            $proveedor->delete();

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el proveedor: ' . $e->getMessage()]);
        }
    }
}
