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
        $query = Proveedor::query();

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('ci_nit', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        $proveedores = $query->withCount('compras')->orderBy('nombre')->get();

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['search']) ?: []
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
        // Validación de duplicidad personalizada (CI/NIT y teléfono únicos)
        $existingProveedor = Proveedor::where('ci_nit', $request->ci_nit)
            ->orWhere('telefono', $request->telefono)
            ->first();

        if ($existingProveedor) {
            if ($existingProveedor->ci_nit === $request->ci_nit) {
                return back()->withErrors(['ci_nit' => 'Ya existe un proveedor con este CI/NIT'])
                    ->withInput();
            }
            if ($existingProveedor->telefono === $request->telefono) {
                return back()->withErrors(['telefono' => 'Ya existe un proveedor con este teléfono'])
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60',
            'ci_nit' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
            'ci_nit.required' => 'El CI/NIT es obligatorio',
            'ci_nit.string' => 'El CI/NIT debe ser texto',
            'ci_nit.max' => 'El CI/NIT no puede tener más de 20 caracteres',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.string' => 'El teléfono debe ser texto',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Proveedor::create($request->only(['nombre', 'ci_nit', 'telefono']));

        return redirect()->route('proveedores.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Proveedor $proveedor)
    {
        $proveedor->load(['compras' => function ($query) {
            $query->with('detalles')->orderBy('fecha', 'desc')->limit(10);
        }]);

        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
            'compras' => $proveedor->compras
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Get fresh data from database using the ID directly
        $proveedor = Proveedor::findOrFail($id);

        return Inertia::render('Proveedores/Edit', [
            'proveedor' => [
                'id_proveedor' => $proveedor->id_proveedor,
                'nombre' => $proveedor->nombre,
                'ci_nit' => $proveedor->ci_nit,
                'telefono' => $proveedor->telefono
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        // Validación de duplicidad personalizada (CI/NIT y teléfono únicos, excluyendo el proveedor actual)
        $existingProveedor = Proveedor::where('id_proveedor', '!=', $proveedor->id_proveedor)
            ->where(function($query) use ($request) {
                $query->where('ci_nit', $request->ci_nit)
                      ->orWhere('telefono', $request->telefono);
            })
            ->first();

        if ($existingProveedor) {
            if ($existingProveedor->ci_nit === $request->ci_nit) {
                return back()->withErrors(['ci_nit' => 'Ya existe un proveedor con este CI/NIT'])
                    ->withInput();
            }
            if ($existingProveedor->telefono === $request->telefono) {
                return back()->withErrors(['telefono' => 'Ya existe un proveedor con este teléfono'])
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60',
            'ci_nit' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
            'ci_nit.required' => 'El CI/NIT es obligatorio',
            'ci_nit.string' => 'El CI/NIT debe ser texto',
            'ci_nit.max' => 'El CI/NIT no puede tener más de 20 caracteres',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.string' => 'El teléfono debe ser texto',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $proveedor->update($request->only(['nombre', 'ci_nit', 'telefono']));

        return redirect()->route('proveedores.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        // Verificar si tiene compras asociadas
        if ($proveedor->compras()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el proveedor porque tiene compras asociadas');
        }

        $proveedor->delete();

        // Debug: Log the success message
        \Log::info('Proveedor eliminado exitosamente', ['proveedor_id' => $id]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
