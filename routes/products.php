<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'user.status', 'role:Propietario'])->group(function () {

    // Rutas de productos
    Route::resource('productos', ProductoController::class);
    Route::get('/productos/descripciones', [ProductoController::class, 'getDescripciones'])->name('productos.descripciones');
    Route::get('/productos/modelos', [ProductoController::class, 'getModelos'])->name('productos.modelos');
    Route::post('/productos/modelos', [ProductoController::class, 'createModelo'])->name('productos.modelos.create');
    Route::post('/productos/{producto}', [ProductoController::class, 'updatePost'])->name('productos.update.post');

    // Rutas de categorías
    Route::resource('categorias', CategoriaController::class);
    Route::get('/categorias/{id}/can-delete', [CategoriaController::class, 'canDelete'])->name('categorias.can-delete');

    // Rutas de marcas
    Route::resource('marcas', MarcaController::class);
    Route::get('/marcas/{id}/can-delete', [MarcaController::class, 'canDelete'])->name('marcas.can-delete');

    // Rutas de modelos
    Route::resource('modelos', ModeloController::class);
});
