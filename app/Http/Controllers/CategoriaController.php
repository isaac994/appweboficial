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

        // Filtro multicampo de búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('descripcion', 'like', '%' . $searchTerm . '%');
            });
        }

        $categorias = $query->orderBy('nombre')->get();

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
            'nombre' => 'required|string|max:50|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255'
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
            'nombre.unique' => 'Ya existe una categoría con este nombre',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $categoria = Categoria::create($request->all());

            return redirect()->route('categorias.index');
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
        $productos = $categoria->productos()->with('modelo')->select('id_producto', 'precio_venta', 'id_modelo')->orderBy('id_producto', 'desc')->get();

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
            'nombre' => 'required|string|max:50|unique:categorias,nombre,' . $id . ',id_categoria',
            'descripcion' => 'nullable|string|max:255'
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
            'nombre.unique' => 'Ya existe una categoría con este nombre',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $categoria->update($request->all());

            return redirect()->route('categorias.index');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la categoría: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Check if a categoria can be deleted
     */
    public function canDelete(string $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $productosCount = $categoria->productos()->count();

            return response()->json([
                'can_delete' => $productosCount === 0,
                'productos_count' => $productosCount,
                'categoria_nombre' => $categoria->nombre
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'can_delete' => false,
                'error' => 'Categoría no encontrada'
            ], 404);
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
            $productosCount = $categoria->productos()->count();
            if ($productosCount > 0) {
                return back()->withErrors([
                    'error' => "No se puede eliminar la categoría '{$categoria->nombre}' porque tiene {$productosCount} producto(s) asociado(s). Primero debe eliminar o cambiar la categoría de estos productos."
                ]);
            }

            $categoria->delete();

            return redirect()->route('categorias.index')
                ->with('success', "Categoría '{$categoria->nombre}' eliminada exitosamente");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'La categoría no existe o ya fue eliminada']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar la categoría: ' . $e->getMessage()]);
        }
    }
}
