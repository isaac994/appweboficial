<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;

use App\Http\Controllers\VentaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;

// Rutas públicas
Route::get('/test-csrf', function () {
    return response()->json(['message' => 'CSRF funcionando', 'token' => csrf_token()]);
});

// Ruta de debug para modelos (sin autenticación)
Route::get('/debug/modelos', function() {
    try {
        $modelos = \App\Models\Modelo::with('marca')->get();
        return response()->json([
            'success' => true,
            'count' => $modelos->count(),
            'modelos' => $modelos->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Ruta de debug para verificar datos del controlador
Route::get('/debug/productos-create', function() {
    $categorias = \App\Models\Categoria::orderBy('nombre')->get();
    $marcas = \App\Models\Marca::orderBy('nombre')->get();
    $modelos = \App\Models\Modelo::with('marca')->orderBy('nombre')->get();

    return response()->json([
        'categorias_count' => $categorias->count(),
        'marcas_count' => $marcas->count(),
        'modelos_count' => $modelos->count(),
        'modelos' => $modelos->toArray()
    ]);
});

// Ruta simple para probar modelos
Route::get('/test-modelos', function() {
    $modelos = \App\Models\Modelo::with('marca')->get();
    return response()->json($modelos);
});

// Ruta para probar el controlador de productos
Route::get('/test-productos-create', function() {
    $categorias = \App\Models\Categoria::orderBy('nombre')->get();
    $marcas = \App\Models\Marca::orderBy('nombre')->get();
    $modelos = \App\Models\Modelo::with('marca')->orderBy('nombre')->get();

    return response()->json([
        'success' => true,
        'categorias' => $categorias->count(),
        'marcas' => $marcas->count(),
        'modelos' => $modelos->count(),
        'primer_modelo' => $modelos->first() ? [
            'id' => $modelos->first()->id_modelo,
            'nombre' => $modelos->first()->nombre,
            'marca' => $modelos->first()->marca ? $modelos->first()->marca->nombre : 'Sin marca'
        ] : null
    ]);
});

// Ruta para servir imágenes de productos
Route::get('/productos/imagen/{filename}', function ($filename) {
    $path = storage_path('app/public/productos/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('productos.imagen');



Route::get('/', function () {
    $query = \App\Models\Producto::with(['categoria', 'marca', 'modelo']);

    // Filtros de búsqueda
    if (request('search')) {
        $search = request('search');

        // Búsqueda por estado (activo/inactivo) - tiene prioridad
        if (strtolower($search) === 'activo' || strtolower($search) === 'disponible') {
            $query->whereRaw('
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) >
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
            ');
        } elseif (strtolower($search) === 'inactivo' || strtolower($search) === 'agotado') {
            $query->whereRaw('
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_compras WHERE id_producto = productos.id_producto) <=
                (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE id_producto = productos.id_producto)
            ');
        } else {
            // Búsqueda normal por texto
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('modelo', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('categoria', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('marca', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
    }

    // Filtro por categoría
    if (request('categoria')) {
        $query->where('id_categoria', request('categoria'));
    }

    // Filtro por marca (a través del modelo)
    if (request('marca')) {
        $query->whereHas('modelo', function($q) {
            $q->where('id_marca', request('marca'));
        });
    }

    // Filtro por estado dinámico
    if (request('estado')) {
        if (request('estado') === 'disponible') {
            $query->whereHas('detallesCompra', function ($q) {
                $q->havingRaw('SUM(cantidad) > (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
            });
        } elseif (request('estado') === 'agotado') {
            $query->whereDoesntHave('detallesCompra')
                ->orWhereHas('detallesCompra', function ($q) {
                    $q->havingRaw('SUM(cantidad) <= (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
                });
        }
    }

    // Ordenamiento
    $sortBy = request('sort', 'id_producto');
    $sortOrder = request('order', 'desc');

    $query->orderBy($sortBy, $sortOrder);

    $productos = $query->paginate(12);

    // Calcular estado dinámico para cada producto
    $productos->getCollection()->transform(function ($producto) {
        $producto->estado_disponible = $producto->estado_disponible;
        return $producto;
    });

    // Obtener categorías y marcas para los filtros
    $categorias = \App\Models\Categoria::orderBy('nombre')->get();
    $marcas = \App\Models\Marca::orderBy('nombre')->get();

    return Inertia::render('Welcome', [
        'productos' => $productos,
        'categorias' => $categorias,
        'marcas' => $marcas,
        'filters' => request()->only(['search', 'categoria', 'marca', 'estado', 'sort', 'order'])
    ]);
})->name('home');

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rutas protegidas
Route::middleware(['auth', 'user.status'])->group(function () {
    // Dashboard - Accesible para todos los usuarios autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario - Accesible para todos los usuarios autenticados
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update.auth');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Logout - Accesible para todos los usuarios autenticados
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas solo para Propietarios
Route::middleware(['auth', 'user.status', 'role:Propietario'])->group(function () {
    // Productos - Solo administradores

    // Proveedores - Solo administradores
    Route::resource('proveedores', ProveedorController::class)->parameters([
        'proveedores' => 'proveedor'
    ]);

    // Compras - Solo administradores
    // Rutas específicas antes del resource
    Route::get('/compras-seleccionar-productos', [CompraController::class, 'seleccionarProductos'])->name('compras.seleccionar-productos');
    Route::post('/compras/confirmar', [CompraController::class, 'confirmar'])->name('compras.confirmar');
    Route::get('/compras/confirmar-temp', [CompraController::class, 'confirmarTemp'])->name('compras.confirmar-temp');
    Route::post('/compras/finalizar', [CompraController::class, 'finalizar'])->name('compras.finalizar');
    Route::get('/compras-eliminadas', [CompraController::class, 'eliminadas'])->name('compras.eliminadas');
    Route::post('/compras/{id}/restaurar', [CompraController::class, 'restaurar'])->name('compras.restaurar');
    Route::get('/compras/{compra}/recibo', [CompraController::class, 'recibo'])->name('compras.recibo');

    Route::resource('compras', CompraController::class);

    // Reportes - Solo administradores
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/general', [ReporteController::class, 'general'])->name('reportes.general');
    Route::post('/reportes/general/generar', [ReporteController::class, 'generarReporteGeneral'])->name('reportes.general.generar');
    Route::post('/reportes/general/pdf', [ReporteController::class, 'exportarReporteGeneralPDF'])->name('reportes.general.pdf');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::post('/reportes/inventario/pdf', [ReporteController::class, 'exportarInventarioPDF'])->name('reportes.inventario.pdf');
    Route::get('/reportes/inventario/reportes/pdf', [ReporteController::class, 'generarPDFReportesInventario'])->name('reportes.inventario.reportes.pdf');
    Route::get('/reportes/productos', [ReporteController::class, 'productos'])->name('reportes.productos');
    Route::post('/reportes/productos/generar', [ReporteController::class, 'generarReporteProductos'])->name('reportes.productos.generar');
    Route::get('/reportes/proveedores', [ReporteController::class, 'proveedores'])->name('reportes.proveedores');
    Route::post('/reportes/proveedores/generar', [ReporteController::class, 'generarReporteProveedores'])->name('reportes.generar-proveedores');
    Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
    Route::post('/reportes/clientes/generar', [ReporteController::class, 'generarReporteClientes'])->name('reportes.generar-clientes');
    Route::get('/reportes/compras', [ReporteController::class, 'compras'])->name('reportes.compras');
    Route::post('/reportes/compras', [ReporteController::class, 'compras'])->name('reportes.compras.pdf');
    Route::get('/reportes/compras/pdf', [ReporteController::class, 'generarPDFCompras'])->name('reportes.compras.pdf.direct');
    Route::post('/reportes/compras/generar', [ReporteController::class, 'generarReporteCompras'])->name('reportes.generar-compras');
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::post('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas.pdf');
    Route::get('/reportes/ventas/pdf', [ReporteController::class, 'generarPDFVentas'])->name('reportes.ventas.pdf.direct');
    Route::post('/reportes/ventas/generar', [ReporteController::class, 'generarReporteVentas'])->name('reportes.generar-ventas');
    Route::get('/reportes/test', [ReporteController::class, 'testPdf'])->name('reportes.test');
    Route::get('/reportes/test-simple', [ReporteController::class, 'testSimple'])->name('reportes.test-simple');
    Route::get('/reportes/verificar-datos', [ReporteController::class, 'verificarDatos'])->name('reportes.verificar-datos');
    Route::get('/reportes/diagnosticar', [ReporteController::class, 'diagnosticar'])->name('reportes.diagnosticar');
    Route::get('/reportes/primera-fecha-compra', [ReporteController::class, 'primeraFechaCompra'])->name('reportes.primera-fecha-compra');
    Route::get('/reportes/primera-fecha-venta', [ReporteController::class, 'primeraFechaVenta'])->name('reportes.primera-fecha-venta');

    // Gestión de Usuarios - Solo administradores
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// Rutas para Operadores y Propietarios (Gestión de Ventas y Clientes)
Route::middleware(['auth', 'user.status', 'role_or_permission:Operador|Propietario'])->group(function () {
    // Clientes - Operadores y Administradores
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes-dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');

    // Ventas - Operadores y Administradores
    Route::resource('ventas', VentaController::class);
    Route::get('/ventas/{id}/recibo', [VentaController::class, 'recibo'])->name('ventas.recibo');
    Route::get('/ventas/{id}/confirmar', [VentaController::class, 'confirmar'])->name('ventas.confirmar');
    Route::get('/ventas-confirmar-temp', [VentaController::class, 'confirmarTemp'])->name('ventas.confirmar-temp');
    Route::post('/ventas-finalizar', [VentaController::class, 'finalizarVenta'])->name('ventas.finalizar');
    Route::post('/ventas-cancelar', [VentaController::class, 'cancelarVenta'])->name('ventas.cancelar');
Route::post('/ventas-guardar-edicion', [VentaController::class, 'guardarEdicion'])->name('ventas.guardar-edicion');
Route::post('/ventas-limpiar-edicion', [VentaController::class, 'limpiarEdicion'])->name('ventas.limpiar-edicion');
    Route::get('/ventas-eliminadas', [VentaController::class, 'eliminadas'])->name('ventas.eliminadas');
    Route::get('/ventas-seleccionar-productos', [VentaController::class, 'seleccionarProductos'])->name('ventas.seleccionar-productos');

    // Inventario - Operadores y Administradores
    Route::get('/inventario', [\App\Http\Controllers\InventarioController::class, 'index'])->name('inventario.index')
        ->middleware('permission:inventario.view');
    Route::get('/inventario/movimientos', [\App\Http\Controllers\InventarioController::class, 'movimientos'])->name('inventario.movimientos')
        ->middleware('permission:inventario.view');
    Route::post('/inventario/movimientos/pdf', [\App\Http\Controllers\InventarioController::class, 'exportarMovimientosPDF'])->name('inventario.movimientos.pdf')
        ->middleware('permission:inventario.view');
    Route::post('/inventario/listado/pdf', [\App\Http\Controllers\InventarioController::class, 'exportarInventarioListadoPDF'])->name('inventario.index.pdf')
        ->middleware('permission:inventario.view');
    Route::get('/inventario/reportes', [\App\Http\Controllers\InventarioController::class, 'reportes'])->name('inventario.reportes')
        ->middleware('permission:inventario.view');
});

// Rutas adicionales con permisos específicos
// Nota: Los operadores NO tienen acceso a productos según los requerimientos

// Ruta de prueba para reportes
Route::get('/test-reporte', [App\Http\Controllers\ReporteController::class, 'testReporte'])->name('test.reporte');

require __DIR__.'/settings.php';
require __DIR__.'/products.php';
require __DIR__.'/auth.php';
