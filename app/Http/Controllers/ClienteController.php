<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::withCount('ventas')
                        ->with(['ventas' => function($query) {
                            $query->latest()->limit(1);
                        }]);

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('apellidos', 'like', '%' . $request->search . '%')
                  ->orWhere('ci', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'nombre');
        $sortDirection = 'asc';

        if ($sortField === 'ventas_count') {
            $query->orderBy('ventas_count', 'desc');
        } elseif ($sortField === 'total_compras') {
            $query->orderByRaw('(SELECT COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) FROM ventas v JOIN detalle_ventas dv ON v.id_venta = dv.id_venta WHERE v.id_cliente = clientes.id_cliente) DESC');
        } elseif ($sortField === 'created_at') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('nombre', 'asc');
        }

        $clientes = $query->paginate(10);

        // Agregar atributos calculados a cada cliente
        $clientes->getCollection()->transform(function ($cliente) {
            $cliente->total_compras = $cliente->ventas()->with('detalles')->get()->sum(function($venta) {
                return $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
            });
            return $cliente;
        });

        // Obtener estadísticas
        $stats = $this->getStats();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only(['search', 'sort']),
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de duplicidad personalizada (CI y teléfono únicos)
        $existingCliente = Cliente::where('ci', $request->ci)
            ->orWhere('telefono', $request->telefono)
            ->first();

        if ($existingCliente) {
            if ($existingCliente->ci === $request->ci) {
                return back()->withErrors(['ci' => 'Ya existe un cliente con este CI'])
                    ->withInput();
            }
            if ($existingCliente->telefono === $request->telefono) {
                return back()->withErrors(['telefono' => 'Ya existe un cliente con este teléfono'])
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'ci' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:25'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 100 caracteres.',
            'ci.max' => 'El CI no puede tener más de 20 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cliente = Cliente::create($request->only(['nombre', 'apellidos', 'ci', 'telefono']));

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with(['ventas' => function($query) {
            $query->with(['detalles.producto'])
                  ->orderBy('created_at', 'desc')
                  ->limit(10);
        }])
        ->withCount('ventas')
        ->findOrFail($id);

        // Asegurar que las ventas tengan sus detalles cargados con productos
        $cliente->ventas->load('detalles.producto');


        // Agregar atributos calculados
        $total_compras = 0;
        foreach ($cliente->ventas as $venta) {
            $total_compras += floatval($venta->total ?? 0);
        }
        $cliente->total_compras = round($total_compras, 2);
        $cliente->ultima_venta = $cliente->ventas->first();

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente->toArray()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cliente = Cliente::findOrFail($id);

        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);

        // Validación de duplicidad personalizada (CI y teléfono únicos, excluyendo el cliente actual)
        $existingCliente = Cliente::where('id_cliente', '!=', $cliente->id_cliente)
            ->where(function($query) use ($request) {
                $query->where('ci', $request->ci)
                      ->orWhere('telefono', $request->telefono);
            })
            ->first();

        if ($existingCliente) {
            if ($existingCliente->ci === $request->ci) {
                return back()->withErrors(['ci' => 'Ya existe un cliente con este CI'])
                    ->withInput();
            }
            if ($existingCliente->telefono === $request->telefono) {
                return back()->withErrors(['telefono' => 'Ya existe un cliente con este teléfono'])
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'ci' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:25'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'apellidos.max' => 'Los apellidos no pueden tener más de 100 caracteres.',
            'ci.max' => 'El CI no puede tener más de 20 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Filtrar campos vacíos para no sobrescribir con strings vacíos
        $data = array_filter($request->only(['nombre', 'apellidos', 'ci', 'telefono']), function($value) {
            return $value !== null && $value !== '';
        });

        $cliente->update($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            // Verificar si tiene ventas asociadas
            if ($cliente->ventas()->count() > 0) {
                return back()->withErrors(['error' => 'No se puede eliminar el cliente porque tiene ventas asociadas']);
            }

            $cliente->delete();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene estadísticas generales
     */
    private function getStats()
    {
        $totalClientes = Cliente::count();
        $totalVentas = \App\Models\Venta::with('detalles')->get()->sum(function($venta) {
            return $venta->detalles->sum(function($detalle) {
                return $detalle->cantidad * $detalle->precio_unitario;
            });
        });
        $totalTransacciones = \App\Models\Venta::count();
        $promedioVentas = $totalClientes > 0 ? $totalVentas / $totalClientes : 0;

        return [
            'total_clientes' => $totalClientes,
            'total_ventas' => $totalVentas,
            'total_transacciones' => $totalTransacciones,
            'promedio_ventas' => round($promedioVentas, 2)
        ];
    }



    /**
     * Muestra el dashboard de clientes
     */
    public function dashboard()
    {
        // Estadísticas generales
        $stats = [
            'total_clientes' => Cliente::count(),
            'total_ventas' => \App\Models\Venta::with('detalles')->get()->sum(function($venta) {
                return $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
            }),
            'total_transacciones' => \App\Models\Venta::count(),
            'promedio_ventas' => \App\Models\Venta::with('detalles')->get()->avg(function($venta) {
                return $venta->detalles->sum(function($detalle) {
                    return $detalle->cantidad * $detalle->precio_unitario;
                });
            }) ?? 0,
            'nuevos_este_mes' => Cliente::whereMonth('created_at', now()->month)->count()
        ];

        // Top 5 clientes por ventas
        $topClientes = Cliente::withCount('ventas')
            ->whereHas('ventas')
            ->orderByRaw('(SELECT COALESCE(SUM(dv.cantidad * dv.precio_unitario), 0) FROM ventas v JOIN detalle_ventas dv ON v.id_venta = dv.id_venta WHERE v.id_cliente = clientes.id_cliente) DESC')
            ->limit(5)
            ->get()
            ->map(function ($cliente) {
                $cliente->total_compras = $cliente->ventas()->with('detalles')->get()->sum(function($venta) {
                    return $venta->detalles->sum(function($detalle) {
                        return $detalle->cantidad * $detalle->precio_unitario;
                    });
                });
                return $cliente;
            });

        // Clientes recientes
        $clientesRecientes = Cliente::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Clientes/Dashboard', [
            'stats' => $stats,
            'topClientes' => $topClientes,
            'clientesRecientes' => $clientesRecientes
        ]);
    }
}
