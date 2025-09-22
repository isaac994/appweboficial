<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;

use App\Http\Controllers\VentaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;

// Rutas públicas
Route::get('/test-csrf', function () {
    return response()->json(['message' => 'CSRF funcionando', 'token' => csrf_token()]);
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
    $query = \App\Models\Producto::with(['categoria', 'marca']);

    // Filtros de búsqueda
    if (request('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%")
              ->orWhereHas('categoria', function($q) use ($search) {
                  $q->where('nombre', 'like', "%{$search}%");
              })
              ->orWhereHas('marca', function($q) use ($search) {
                  $q->where('nombre', 'like', "%{$search}%");
              });
        });
    }

    // Filtro por categoría
    if (request('categoria')) {
        $query->where('id_categoria', request('categoria'));
    }

    // Filtro por marca
    if (request('marca')) {
        $query->where('id_marca', request('marca'));
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
    $sortBy = request('sort', 'nombre');
    $sortOrder = request('order', 'asc');
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('permission:dashboard.view');

    // Perfil de usuario - Accesible para todos los usuarios autenticados
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update.auth');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Logout - Accesible para todos los usuarios autenticados
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas solo para Administradores
Route::middleware(['auth', 'user.status', 'role:Administrador'])->group(function () {
    // Productos - Solo administradores
    Route::resource('productos', ProductoController::class);
    Route::post('/productos/{producto}', [ProductoController::class, 'updatePost'])->name('productos.update.post');

    // Categorías - Solo administradores
    Route::resource('categorias', CategoriaController::class);
    Route::get('/categorias/{id}/can-delete', [CategoriaController::class, 'canDelete'])->name('categorias.can-delete');

    // Marcas - Solo administradores
    Route::resource('marcas', MarcaController::class);
    Route::get('/marcas/{id}/can-delete', [MarcaController::class, 'canDelete'])->name('marcas.can-delete');

    // Proveedores - Solo administradores
    Route::resource('proveedores', ProveedorController::class)->parameters([
        'proveedores' => 'proveedor'
    ]);

    // Compras - Solo administradores
    Route::resource('compras', CompraController::class);
    Route::get('/compras/{compra}/recibo', [CompraController::class, 'recibo'])->name('compras.recibo');

    // Reportes - Solo administradores
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/productos', [ReporteController::class, 'productos'])->name('reportes.productos');
    Route::post('/reportes/productos/generar', [ReporteController::class, 'generarReporteProductos'])->name('reportes.productos.generar');
    Route::get('/reportes/compras', [ReporteController::class, 'compras'])->name('reportes.compras');
    Route::post('/reportes/compras/generar', [ReporteController::class, 'generarReporteCompras'])->name('reportes.compras.generar');
    Route::get('/reportes/proveedores', [ReporteController::class, 'proveedores'])->name('reportes.proveedores');
    Route::post('/reportes/proveedores/generar', [ReporteController::class, 'generarReporteProveedores'])->name('reportes.generar-proveedores');
    Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
    Route::post('/reportes/clientes/generar', [ReporteController::class, 'generarReporteClientes'])->name('reportes.generar-clientes');
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::post('/reportes/ventas/generar', [ReporteController::class, 'generarReporteVentas'])->name('reportes.generar-ventas');
    Route::get('/reportes/test', [ReporteController::class, 'testPdf'])->name('reportes.test');

    // Gestión de Usuarios - Solo administradores
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// Rutas para Operadores y Administradores (Gestión de Ventas y Clientes)
Route::middleware(['auth', 'user.status', 'role:Operador|Administrador'])->group(function () {
    // Clientes - Operadores y Administradores
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes-dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');

    // Ventas - Operadores y Administradores
    Route::resource('ventas', VentaController::class);
    Route::get('/ventas/{id}/recibo', [VentaController::class, 'recibo'])->name('ventas.recibo');
});

// Rutas adicionales con permisos específicos
// Nota: Los operadores NO tienen acceso a productos según los requerimientos

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
