<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;

// Rutas públicas
Route::get('/', function () {
    $productosRecientes = \App\Models\Producto::with(['categoria', 'marca'])
        ->orderBy('created_at', 'desc')
        ->limit(8)
        ->get();

    return Inertia::render('Welcome', [
        'productosRecientes' => $productosRecientes
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
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Productos
    Route::resource('productos', ProductoController::class);
    Route::post('/productos/{producto}', [ProductoController::class, 'updatePost'])->name('productos.update.post');

    // Categorías
    Route::resource('categorias', CategoriaController::class);

    // Marcas
    Route::resource('marcas', MarcaController::class);

    // Proveedores
    Route::resource('proveedores', ProveedorController::class);

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes-dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');

    // Ventas
    Route::resource('ventas', VentaController::class);

    // Compras
    Route::resource('compras', CompraController::class);

    // Perfil de usuario
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update.auth');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
