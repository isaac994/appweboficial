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

        // Filtros
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $marcas = $query->orderBy('nombre')->paginate(5);

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
            'nombre' => 'required|string|max:60',
            'pais_origen' => 'nullable|string|max:60'
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
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
            'nombre' => 'required|string|max:60',
            'pais_origen' => 'nullable|string|max:60'
        ], [
            'nombre.required' => 'El nombre de la marca es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $marca = Marca::findOrFail($id);

            // Verificar si tiene productos asociados
            if ($marca->productos()->count() > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar la marca porque tiene productos asociados']);
            }

            $marca->delete();

            return redirect()->route('marcas.index')
                ->with('success', 'Marca eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar la marca: ' . $e->getMessage()]);
        }
    }
}
