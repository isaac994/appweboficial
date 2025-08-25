<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Categoria::withCount('productos');

        // Filtros
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $categorias = $query->orderBy('nombre')->paginate(5);

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Categorias/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:255'
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $categoria = Categoria::create($request->all());

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría creada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la categoría: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $productos = $categoria->productos()->select('id_producto', 'nombre', 'precio_venta')->orderBy('nombre')->get();

        return Inertia::render('Categorias/Show', [
            'categoria' => $categoria,
            'productos' => $productos
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:255'
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $categoria->update($request->all());

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la categoría: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            // Verificar si tiene productos asociados
            if ($categoria->productos()->count() > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar la categoría porque tiene productos asociados']);
            }

            $categoria->delete();

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar la categoría: ' . $e->getMessage()]);
        }
    }
}
