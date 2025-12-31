<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use App\Models\Marca;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModeloController extends Controller
{
    public function index(Request $request)
    {
        $query = Modelo::with('marca');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('marca', function ($marcaQuery) use ($search) {
                      $marcaQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por marca
        if ($request->filled('marca_id')) {
            $query->where('id_marca', $request->marca_id);
        }

        $modelos = $query->paginate(10)->withQueryString();
        $marcas = Marca::all();

        return Inertia::render('Modelos/Index', [
            'modelos' => $modelos,
            'marcas' => $marcas,
            'filters' => $request->only(['search', 'marca_id'])
        ]);
    }

    public function create()
    {
        $marcas = Marca::all();

        return Inertia::render('Modelos/Create', [
            'marcas' => $marcas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'id_marca' => 'required|exists:marcas,id_marca'
        ]);

        Modelo::create($request->all());

        return redirect()->route('modelos.index');
    }

    public function show(Modelo $modelo)
    {
        $modelo->load('marca');

        return Inertia::render('Modelos/Show', [
            'modelo' => $modelo
        ]);
    }

    public function edit(Modelo $modelo)
    {
        $marcas = Marca::all();

        return Inertia::render('Modelos/Edit', [
            'modelo' => $modelo,
            'marcas' => $marcas
        ]);
    }

    public function update(Request $request, Modelo $modelo)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'id_marca' => 'required|exists:marcas,id_marca'
        ]);

        $modelo->update($request->all());

        return redirect()->route('modelos.index');
    }

    public function destroy(Modelo $modelo)
    {
        // Verificar si tiene productos asociados
        if ($modelo->productos()->count() > 0) {
            return back()->withErrors([
                'error' => 'No se puede eliminar el modelo porque tiene productos asociados.'
            ]);
        }

        $modelo->delete();

        return redirect()->route('modelos.index')
            ->with('success', 'Modelo eliminado exitosamente');
    }
}
