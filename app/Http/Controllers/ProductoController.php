<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca', 'modelo']);

        // Búsqueda multicampo potente
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                // Búsqueda por descripción del producto
                $q->where('descripcion', 'like', '%' . $searchTerm . '%')
                  // Búsqueda por precio (solo si es numérico)
                  ->orWhere('precio_venta', 'like', '%' . $searchTerm . '%')
                  // Búsqueda por categoría
                  ->orWhereHas('categoria', function ($subQ) use ($searchTerm) {
                      $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                  })
                  // Búsqueda por marca (a través del modelo)
                  ->orWhereHas('modelo.marca', function ($subQ) use ($searchTerm) {
                      $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                  })
                  // Búsqueda por modelo
                  ->orWhereHas('modelo', function ($subQ) use ($searchTerm) {
                      $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                  })
                  // Búsqueda por estado (disponible/agotado)
                  ->orWhere(function ($statusQ) use ($searchTerm) {
                      if (strtolower($searchTerm) === 'disponible' || strtolower($searchTerm) === 'activo') {
                          $statusQ->whereHas('detallesCompra', function ($q) {
                              $q->whereHas('compra', function ($compraQuery) {
                                  $compraQuery->where('estado', false); // Solo compras activas
                              })->havingRaw('SUM(cantidad) > (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
                          });
                      } elseif (strtolower($searchTerm) === 'agotado' || strtolower($searchTerm) === 'inactivo') {
                          $statusQ->whereDoesntHave('detallesCompra', function ($q) {
                              $q->whereHas('compra', function ($compraQuery) {
                                  $compraQuery->where('estado', false); // Solo compras activas
                              });
                          })->orWhereHas('detallesCompra', function ($q) {
                              $q->whereHas('compra', function ($compraQuery) {
                                  $compraQuery->where('estado', false); // Solo compras activas
                              })->havingRaw('SUM(cantidad) <= (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
                          });
                      }
                  });
            });
        }


        $productos = $query->orderBy('id_producto', 'desc')->paginate(8);

        // Calcular estado dinámico y stock disponible para cada producto
        $productos->getCollection()->transform(function ($producto) {
            $producto->estado_disponible = $producto->estado_disponible;
            $producto->stock_disponible = $producto->stock_disponible;

            // Asegurar que la URL de la imagen sea completa
            if ($producto->img_url) {
                // Extraer el nombre del archivo de la URL
                $filename = basename($producto->img_url);
                // Usar la ruta específica para servir imágenes
                $producto->img_url = route('productos.imagen', ['filename' => $filename]);
            }

            return $producto;
        });

        // Obtener datos para filtros
        $categorias = Categoria::orderBy('nombre')->get();
        // Ya no necesitamos enviar marcas al frontend, se obtienen a través de los modelos
        $modelos = Modelo::with('marca')->orderBy('nombre')->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'modelos' => $modelos,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Get existing descripciones for autocomplete
     */
    public function getDescripciones(Request $request)
    {
        $query = Producto::select('descripcion')
            ->whereNotNull('descripcion')
            ->where('descripcion', '!=', '')
            ->distinct();

        // Si se proporciona un filtro de búsqueda
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', '%' . $request->search . '%');
        }

        $descripciones = $query->orderBy('descripcion')
            ->limit(20)
            ->pluck('descripcion');

        return response()->json($descripciones);
    }

    /**
     * Get modelos for a specific marca
     */
    public function getModelos(Request $request)
    {
        $marcaId = $request->input('marca_id');

        if (!$marcaId) {
            return response()->json([]);
        }

        $modelos = Modelo::where('id_marca', $marcaId)
            ->orderBy('nombre')
            ->get();

        return response()->json($modelos);
    }

    /**
     * Create a new modelo dynamically
     */
    public function createModelo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'id_marca' => 'required|exists:marcas,id_marca'
        ], [
            'nombre.required' => 'El nombre del modelo es obligatorio',
            'nombre.string' => 'El nombre del modelo debe ser texto',
            'nombre.max' => 'El nombre del modelo no puede exceder 255 caracteres',
            'id_marca.required' => 'La marca es obligatoria',
            'id_marca.exists' => 'La marca seleccionada no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar si ya existe un modelo con el mismo nombre para la misma marca
            $existingModelo = Modelo::where('nombre', $request->nombre)
                ->where('id_marca', $request->id_marca)
                ->first();

            if ($existingModelo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un modelo con ese nombre para esta marca'
                ], 422);
            }

            $modelo = Modelo::create([
                'nombre' => $request->nombre,
                'id_marca' => $request->id_marca
            ]);

            return response()->json([
                'success' => true,
                'modelo' => $modelo->load('marca')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el modelo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $modelos = Modelo::with('marca')->orderBy('nombre')->get();


        // Cargar descripciones existentes para autocompletado
        $descripciones = Producto::select('descripcion')
            ->whereNotNull('descripcion')
            ->where('descripcion', '!=', '')
            ->distinct()
            ->orderBy('descripcion')
            ->limit(50)
            ->pluck('descripcion');


        // Log temporal para verificar datos
        \Log::info('Datos enviados a Inertia:', [
            'modelos_count' => $modelos->count(),
            'primer_modelo' => $modelos->first() ? $modelos->first()->toArray() : null
        ]);

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias->toArray(),
            'marcas' => $marcas->toArray(),
            'modelos' => $modelos->toArray(),
            'descripciones' => $descripciones->toArray()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Iniciando creación de producto', $request->all());

        // Validar duplicidad: mismo modelo + categoría + descripción
        if ($request->filled('id_modelo') && $request->filled('id_categoria')) {
            $query = Producto::where('id_modelo', $request->id_modelo)
                ->where('id_categoria', $request->id_categoria);

            // Si hay descripción, también validar por descripción
            if ($request->filled('descripcion') && !empty(trim($request->descripcion))) {
                $query->where('descripcion', $request->descripcion);
            } else {
                // Si no hay descripción, buscar productos sin descripción
                $query->where(function($q) {
                    $q->whereNull('descripcion')->orWhere('descripcion', '');
                });
            }

            $existingProduct = $query->first();

            if ($existingProduct) {
                \Log::warning('Producto duplicado encontrado', [
                    'id_modelo' => $request->id_modelo,
                    'id_categoria' => $request->id_categoria,
                    'descripcion' => $request->descripcion,
                    'existing_product' => $existingProduct->id_producto
                ]);

                $message = '⚠️ Ya existe un producto con la misma combinación de modelo, categoría y descripción. No se permiten productos duplicados.';

                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'type' => 'duplicidad'
                    ], 422);
                }
                return back()->withErrors(['duplicidad' => $message])->withInput();
            }
        }

        // Verificar si se está creando un modelo nuevo
        $modeloId = null;
        if ($request->filled('modelo_nuevo') && $request->filled('id_marca')) {
            // Crear el modelo nuevo
            $modeloExistente = Modelo::where('nombre', $request->modelo_nuevo)
                ->where('id_marca', $request->id_marca)
                ->first();

            if ($modeloExistente) {
                $modeloId = $modeloExistente->id_modelo;
            } else {
                // Crear el nuevo modelo
                $nuevoModelo = Modelo::create([
                    'nombre' => $request->modelo_nuevo,
                    'id_marca' => $request->id_marca
                ]);
                $modeloId = $nuevoModelo->id_modelo;
                \Log::info('Modelo nuevo creado', ['modelo_id' => $modeloId, 'nombre' => $request->modelo_nuevo]);
            }
        } else {
            $modeloId = $request->id_modelo;
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_marca' => 'nullable|exists:marcas,id_marca',
            'id_modelo' => 'nullable|exists:modelos,id_modelo',
            'modelo_nuevo' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB máximo
        ], [
            'precio_venta.required' => 'El precio de venta es obligatorio',
            'precio_venta.numeric' => 'El precio de venta debe ser un número',
            'precio_venta.min' => 'El precio de venta debe ser mayor a 0',
            'id_categoria.required' => 'La categoría es obligatoria',
            'id_categoria.exists' => 'La categoría seleccionada no existe',
            'id_marca.exists' => 'La marca seleccionada no existe',
            'id_modelo.exists' => 'El modelo seleccionado no existe',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif',
            'imagen.max' => 'La imagen no debe superar los 5MB'
        ]);

        if ($validator->fails()) {
            \Log::error('Validación fallida', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        \Log::info('Validación exitosa, procediendo a crear producto');

        try {
            $data = $request->only(['descripcion', 'precio_venta', 'id_categoria']);
            $data['id_modelo'] = $modeloId;

            // Manejar la subida de imagen
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreArchivo = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('productos', $nombreArchivo, 'public');
                $data['img_url'] = route('productos.imagen', ['filename' => $nombreArchivo]);
            }

            $producto = Producto::create($data);

            \Log::info('Producto creado exitosamente', ['producto_id' => $producto->id_producto]);

            return redirect()->route('productos.index');
        } catch (\Exception $e) {
            \Log::error('Error al crear producto: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el producto: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $producto = Producto::with(['categoria', 'marca', 'modelo'])->findOrFail($id);

        // Agregar estado dinámico
        $producto->estado_disponible = $producto->estado_disponible;

        return Inertia::render('Productos/Show', [
            'producto' => $producto
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $producto = Producto::with(['categoria', 'marca', 'modelo'])->findOrFail($id);

        // Agregar estado dinámico
        $producto->estado_disponible = $producto->estado_disponible;

        $categorias = Categoria::orderBy('nombre')->get();
        // Ya no necesitamos enviar marcas al frontend, se obtienen a través de los modelos
        $modelos = Modelo::with('marca')->orderBy('nombre')->get();

        // Cargar descripciones existentes para autocompletado
        $descripciones = Producto::select('descripcion')
            ->whereNotNull('descripcion')
            ->where('descripcion', '!=', '')
            ->distinct()
            ->orderBy('descripcion')
            ->limit(50)
            ->pluck('descripcion');

        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias,
            'modelos' => $modelos,
            'descripciones' => $descripciones
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        // Validar duplicidad: mismo modelo + categoría + descripción (excluyendo el producto actual)
        if ($request->has('id_modelo') && $request->has('id_categoria')) {
            $query = Producto::where('id_modelo', $request->id_modelo)
                ->where('id_categoria', $request->id_categoria)
                ->where('id_producto', '!=', $id); // Excluir el producto actual

            // Si hay descripción, también validar por descripción
            if ($request->has('descripcion') && !empty(trim($request->descripcion))) {
                $query->where('descripcion', $request->descripcion);
            } else {
                // Si no hay descripción, buscar productos sin descripción
                $query->where(function($q) {
                    $q->whereNull('descripcion')->orWhere('descripcion', '');
                });
            }

            $existingProduct = $query->first();

            if ($existingProduct) {
                $message = '⚠️ Ya existe un producto con la misma combinación de modelo, categoría y descripción. No se permiten productos duplicados.';

                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'type' => 'duplicidad'
                    ], 422);
                }
                return back()->withErrors(['duplicidad' => $message])->withInput();
            }
        }

        // Solo validar campos que se envían
        $rules = [];
        $messages = [];

        if ($request->has('id_modelo')) {
            // Verificar si la categoría es "Accesorio Simple"
            $categoria = null;
            if ($request->has('id_categoria')) {
                $categoria = \App\Models\Categoria::find($request->id_categoria);
            }

            $isAccesorioSimple = $categoria && stripos($categoria->nombre, 'accesorio simple') !== false;

            if ($isAccesorioSimple) {
                // Para Accesorio Simple, el modelo es opcional
                $rules['id_modelo'] = 'nullable|exists:modelos,id_modelo';
            } else {
                // Para otras categorías, el modelo es obligatorio
                $rules['id_modelo'] = 'required|exists:modelos,id_modelo';
                $messages['id_modelo.required'] = 'El modelo es obligatorio';
            }
            $messages['id_modelo.exists'] = 'El modelo seleccionado no existe';
        }

        if ($request->has('descripcion')) {
            $rules['descripcion'] = 'nullable|string';
        }

        if ($request->has('estado')) {
            $rules['estado'] = 'required|in:activo,inactivo';
            $messages['estado.required'] = 'El estado es obligatorio';
            $messages['estado.in'] = 'El estado debe ser activo o inactivo';
        }

        if ($request->has('precio_venta')) {
            $rules['precio_venta'] = 'required|numeric|min:0';
            $messages['precio_venta.required'] = 'El precio de venta es obligatorio';
            $messages['precio_venta.numeric'] = 'El precio de venta debe ser un número';
            $messages['precio_venta.min'] = 'El precio de venta debe ser mayor a 0';
        }

        if ($request->has('id_categoria')) {
            $rules['id_categoria'] = 'required|exists:categorias,id_categoria';
            $messages['id_categoria.required'] = 'La categoría es obligatoria';
            $messages['id_categoria.exists'] = 'La categoría seleccionada no existe';
        }


        if ($request->hasFile('imagen')) {
            $rules['imagen'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB máximo
            $messages['imagen.image'] = 'El archivo debe ser una imagen';
            $messages['imagen.mimes'] = 'La imagen debe ser de tipo: jpeg, png, jpg, gif';
            $messages['imagen.max'] = 'La imagen no debe superar los 5MB';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->only(['descripcion', 'precio_venta', 'id_categoria', 'id_modelo']);

            // Manejar la subida de imagen
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($producto->img_url) {
                    $rutaAnterior = str_replace('/storage/', '', $producto->img_url);
                    if (Storage::disk('public')->exists($rutaAnterior)) {
                        Storage::disk('public')->delete($rutaAnterior);
                    }
                }

                $imagen = $request->file('imagen');
                $nombreArchivo = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('productos', $nombreArchivo, 'public');
                $data['img_url'] = route('productos.imagen', ['filename' => $nombreArchivo]);
            }

            $producto->update($data);

            return redirect()->route('productos.index');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el producto: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified resource in storage via POST (for file uploads).
     */
    public function updatePost(Request $request, string $id)
    {
        return $this->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $producto = Producto::findOrFail($id);

            // Verificar si el producto tiene stock disponible
            $stockDisponible = $producto->stock_disponible;
            if ($stockDisponible > 0) {
                return back()->withErrors([
                    'error' => "No se puede eliminar el producto '{$producto->modelo?->nombre}' porque tiene stock disponible ({$stockDisponible} unidades). Primero debe vender todo el stock o ajustar las cantidades."
                ]);
            }

            // Verificar si el producto tiene historial de compras
            $totalCompras = $producto->detallesCompra()
                ->whereHas('compra', function ($query) {
                    $query->where('estado', false); // Solo compras activas
                })
                ->sum('cantidad');

            if ($totalCompras > 0) {
                return back()->withErrors([
                    'error' => "No se puede eliminar el producto '{$producto->modelo?->nombre}' porque tiene historial de compras ({$totalCompras} unidades compradas). Para mantener la integridad de los datos, no se puede eliminar un producto que ha sido comprado."
                ]);
            }

            // Verificar si el producto tiene historial de ventas
            $totalVentas = $producto->detallesVenta()->sum('cantidad');
            if ($totalVentas > 0) {
                return back()->withErrors([
                    'error' => "No se puede eliminar el producto '{$producto->modelo?->nombre}' porque tiene historial de ventas ({$totalVentas} unidades vendidas). Para mantener la integridad de los datos, no se puede eliminar un producto que ha sido vendido."
                ]);
            }

            // Si llegamos aquí, el producto se puede eliminar
            // Eliminar imagen si existe
            if ($producto->img_url) {
                $ruta = str_replace('/storage/', '', $producto->img_url);
                if (Storage::disk('public')->exists($ruta)) {
                    Storage::disk('public')->delete($ruta);
                }
            }

            $producto->delete();

            return redirect()->route('productos.index')
                ->with('success', 'Producto eliminado exitosamente');
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar errores de restricción de clave foránea
            if ($e->getCode() == 23000) {
                $productoNombre = $producto->modelo?->nombre ?? 'este producto';

                if (str_contains($e->getMessage(), 'detalle_compras')) {
                    return back()->withErrors([
                        'error' => "No se puede eliminar el producto '{$productoNombre}' porque tiene historial de compras. Para mantener la integridad de los datos, no se puede eliminar un producto que ha sido comprado."
                    ]);
                }

                if (str_contains($e->getMessage(), 'detalle_ventas')) {
                    return back()->withErrors([
                        'error' => "No se puede eliminar el producto '{$productoNombre}' porque tiene historial de ventas. Para mantener la integridad de los datos, no se puede eliminar un producto que ha sido vendido."
                    ]);
                }

                return back()->withErrors([
                    'error' => "No se puede eliminar el producto '{$productoNombre}' porque está siendo utilizado en otras operaciones del sistema. Para mantener la integridad de los datos, no se puede eliminar este producto."
                ]);
            }

            return back()->withErrors(['error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        }
    }
}
