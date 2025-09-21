<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Marca::withCount('productos');

        // Filtro multicampo de búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pais_origen', 'like', '%' . $searchTerm . '%');
            });
        }

        $marcas = $query->orderBy('nombre')->get();

        return Inertia::render('Marcas/Index', [
            'marcas' => $marcas,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Marcas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:marcas,nombre',
            'pais_origen' => 'nullable|string|max:60'
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
            'nombre.unique' => 'Ya existe una marca con este nombre',
            'pais_origen.max' => 'El país de origen no puede tener más de 60 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $marca = Marca::create($request->all());

            return redirect()->route('marcas.index')
                ->with('success', 'Marca creada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la marca: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $marca = Marca::findOrFail($id);
        $productos = $marca->productos()->select('id_producto', 'nombre', 'precio_venta')->orderBy('nombre')->get();

        return Inertia::render('Marcas/Show', [
            'marca' => $marca,
            'productos' => $productos
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $marca = Marca::findOrFail($id);

        return Inertia::render('Marcas/Edit', [
            'marca' => $marca
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $marca = Marca::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:marcas,nombre,' . $id . ',id_marca',
            'pais_origen' => 'nullable|string|max:60'
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
            'nombre.unique' => 'Ya existe una marca con este nombre',
            'pais_origen.max' => 'El país de origen no puede tener más de 60 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $marca->update($request->all());

            return redirect()->route('marcas.index')
                ->with('success', 'Marca actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la marca: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Check if a marca can be deleted
     */
    public function canDelete(string $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $productosCount = $marca->productos()->count();

            return response()->json([
                'can_delete' => $productosCount === 0,
                'productos_count' => $productosCount,
                'marca_nombre' => $marca->nombre
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'can_delete' => false,
                'error' => 'Marca no encontrada'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $marca = Marca::findOrFail($id);

            // Verificar si tiene productos asociados
            $productosCount = $marca->productos()->count();
            if ($productosCount > 0) {
                return back()->withErrors([
                    'error' => "No se puede eliminar la marca '{$marca->nombre}' porque tiene {$productosCount} producto(s) asociado(s). Primero debe eliminar o cambiar la marca de estos productos."
                ]);
            }

            $marca->delete();

            return redirect()->route('marcas.index')
                ->with('success', "Marca '{$marca->nombre}' eliminada exitosamente");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'La marca no existe o ya fue eliminada']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar la marca: ' . $e->getMessage()]);
        }
    }
}
