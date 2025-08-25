<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_productos' => Producto::count(),
            'total_categorias' => Categoria::count(),
            'total_marcas' => Marca::count(),
            'total_proveedores' => Proveedor::count(),
            'total_clientes' => Cliente::count(),
        ];

        $productos_recientes = Producto::with(['categoria', 'marca'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $clientes_recientes = Cliente::orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'productos_recientes' => $productos_recientes,
            'clientes_recientes' => $clientes_recientes,
        ]);
    }
}
