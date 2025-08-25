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
                        ->withSum('ventas', 'total')
                        ->with(['ventas' => function($query) {
                            $query->latest()->limit(1);
                        }]);

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('correo_electronico', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por estado de fidelización
        if ($request->filled('fidelizacion')) {
            $query->whereRaw('CASE
                WHEN (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_cliente = clientes.id_cliente) >= 1000 THEN "Premium"
                WHEN (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_cliente = clientes.id_cliente) >= 500 THEN "Oro"
                WHEN (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_cliente = clientes.id_cliente) >= 100 THEN "Plata"
                ELSE "Bronce"
            END = ?', [$request->fidelizacion]);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'nombre');
        $sortDirection = 'asc';

        if ($sortField === 'ventas_count') {
            $query->orderBy('ventas_count', 'desc');
        } elseif ($sortField === 'total_compras') {
            $query->orderByRaw('(SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_cliente = clientes.id_cliente) DESC');
        } elseif ($sortField === 'created_at') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('nombre', 'asc');
        }

        $clientes = $query->paginate(10);

        // Agregar atributos calculados a cada cliente
        $clientes->getCollection()->transform(function ($cliente) {
            $cliente->total_compras = $cliente->ventas_sum_total ?? 0;
            $cliente->estado_fidelizacion = $this->getEstadoFidelizacion($cliente->total_compras);
            return $cliente;
        });

        // Obtener estadísticas
        $stats = $this->getStats();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only(['search', 'genero', 'fidelizacion', 'sort']),
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
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:25',
            'direccion' => 'nullable|string|max:255',
            'correo_electronico' => 'nullable|email|max:150|unique:clientes,correo_electronico'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'correo_electronico.email' => 'El correo electrónico debe tener un formato válido.',
            'correo_electronico.max' => 'El correo electrónico no puede tener más de 150 caracteres.',
            'correo_electronico.unique' => 'Este correo electrónico ya está registrado.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cliente = Cliente::create($request->only(['nombre', 'telefono', 'direccion', 'correo_electronico']));

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
        ->withSum('ventas', 'total')
        ->findOrFail($id);

        // Agregar atributos calculados
        $cliente->total_compras = $cliente->ventas_sum_total ?? 0;
        $cliente->estado_fidelizacion = $this->getEstadoFidelizacion($cliente->total_compras);
        $cliente->ultima_venta = $cliente->ventas->first();

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente
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

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:25',
            'direccion' => 'nullable|string|max:255',
            'correo_electronico' => [
                'nullable',
                'email',
                'max:150',
                function ($attribute, $value, $fail) use ($id) {
                    // Solo validar unique si el correo no está vacío
                    if (!empty($value)) {
                        $exists = \App\Models\Cliente::where('correo_electronico', $value)
                            ->where('id_cliente', '!=', $id)
                            ->exists();

                        if ($exists) {
                            $fail('Este correo electrónico ya está registrado.');
                        }
                    }
                }
            ]
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 25 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'correo_electronico.email' => 'El correo electrónico debe tener un formato válido.',
            'correo_electronico.max' => 'El correo electrónico no puede tener más de 150 caracteres.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Filtrar campos vacíos para no sobrescribir con strings vacíos
        $data = array_filter($request->only(['nombre', 'telefono', 'direccion', 'correo_electronico']), function($value) {
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
        $totalVentas = \App\Models\Venta::sum('total');
        $totalTransacciones = \App\Models\Venta::count();
        $promedioVentas = $totalClientes > 0 ? $totalVentas / $totalClientes : 0;

        // Contar clientes premium (con más de $1000 en compras)
        $clientesPremium = Cliente::select('clientes.id_cliente')
            ->join('ventas', 'clientes.id_cliente', '=', 'ventas.id_cliente')
            ->groupBy('clientes.id_cliente')
            ->havingRaw('SUM(ventas.total) >= ?', [1000])
            ->count();

        return [
            'total_clientes' => $totalClientes,
            'total_ventas' => $totalVentas,
            'total_transacciones' => $totalTransacciones,
            'promedio_ventas' => round($promedioVentas, 2),
            'clientes_premium' => $clientesPremium
        ];
    }

    /**
     * Determina el estado de fidelización basado en el total de compras
     */
    private function getEstadoFidelizacion($totalCompras)
    {
        if ($totalCompras >= 1000) {
            return 'Premium';
        } elseif ($totalCompras >= 500) {
            return 'Oro';
        } elseif ($totalCompras >= 100) {
            return 'Plata';
        } else {
            return 'Bronce';
        }
    }

    /**
     * Muestra el dashboard de clientes
     */
    public function dashboard()
    {
        // Estadísticas generales
        $stats = [
            'total_clientes' => Cliente::count(),
            'total_ventas' => \App\Models\Venta::sum('total'),
            'total_transacciones' => \App\Models\Venta::count(),
            'promedio_ventas' => \App\Models\Venta::avg('total') ?? 0,
            'clientes_premium' => Cliente::select('clientes.id_cliente')
                ->join('ventas', 'clientes.id_cliente', '=', 'ventas.id_cliente')
                ->groupBy('clientes.id_cliente')
                ->havingRaw('SUM(ventas.total) >= ?', [1000])
                ->count(),
            'nuevos_este_mes' => Cliente::whereMonth('created_at', now()->month)->count()
        ];

        // Datos de fidelización
        $fidelizacionData = [
            ['nombre' => 'Bronce', 'cantidad' => Cliente::whereDoesntHave('ventas')->count(), 'porcentaje' => 0],
            ['nombre' => 'Plata', 'cantidad' => 0, 'porcentaje' => 0],
            ['nombre' => 'Oro', 'cantidad' => 0, 'porcentaje' => 0],
            ['nombre' => 'Premium', 'cantidad' => 0, 'porcentaje' => 0]
        ];

        // Calcular porcentajes reales
        $totalConVentas = Cliente::whereHas('ventas')->count();
        if ($totalConVentas > 0) {
            $fidelizacionData[1]['cantidad'] = Cliente::select('clientes.id_cliente')
                ->join('ventas', 'clientes.id_cliente', '=', 'ventas.id_cliente')
                ->groupBy('clientes.id_cliente')
                ->havingRaw('SUM(ventas.total) >= ? AND SUM(ventas.total) < ?', [100, 500])
                ->count();

            $fidelizacionData[2]['cantidad'] = Cliente::select('clientes.id_cliente')
                ->join('ventas', 'clientes.id_cliente', '=', 'ventas.id_cliente')
                ->groupBy('clientes.id_cliente')
                ->havingRaw('SUM(ventas.total) >= ? AND SUM(ventas.total) < ?', [500, 1000])
                ->count();

            $fidelizacionData[3]['cantidad'] = Cliente::select('clientes.id_cliente')
                ->join('ventas', 'clientes.id_cliente', '=', 'ventas.id_cliente')
                ->groupBy('clientes.id_cliente')
                ->havingRaw('SUM(ventas.total) >= ?', [1000])
                ->count();

            // Calcular porcentajes
            foreach ($fidelizacionData as &$nivel) {
                $nivel['porcentaje'] = round(($nivel['cantidad'] / $totalConVentas) * 100, 1);
            }
        }

        // Top 5 clientes por ventas
        $topClientes = Cliente::withCount('ventas')
            ->withSum('ventas', 'total')
            ->whereHas('ventas')
            ->orderByRaw('(SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_cliente = clientes.id_cliente) DESC')
            ->limit(5)
            ->get()
            ->map(function ($cliente) {
                $cliente->total_compras = $cliente->ventas_sum_total ?? 0;
                $cliente->estado_fidelizacion = $this->getEstadoFidelizacion($cliente->total_compras);
                return $cliente;
            });

        // Clientes recientes
        $clientesRecientes = Cliente::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Clientes/Dashboard', [
            'stats' => $stats,
            'fidelizacionData' => $fidelizacionData,
            'topClientes' => $topClientes,
            'clientesRecientes' => $clientesRecientes
        ]);
    }
}
