<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

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
        $query = Producto::with(['categoria', 'marca']);

        // Filtro multicampo de búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('descripcion', 'like', '%' . $searchTerm . '%')
                  ->orWhere('precio_venta', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('categoria', function ($subQ) use ($searchTerm) {
                      $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('marca', function ($subQ) use ($searchTerm) {
                      $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('marca')) {
            $query->where('id_marca', $request->marca);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'disponible') {
                $query->whereHas('detallesCompra', function ($q) {
                    $q->havingRaw('SUM(cantidad) > (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
                });
            } elseif ($request->estado === 'agotado') {
                $query->whereDoesntHave('detallesCompra')
                    ->orWhereHas('detallesCompra', function ($q) {
                        $q->havingRaw('SUM(cantidad) <= (SELECT COALESCE(SUM(cantidad), 0) FROM detalle_ventas WHERE detalle_ventas.id_producto = productos.id_producto)');
                    });
            }
        }

        $productos = $query->orderBy('nombre')->paginate(6);

        // Calcular estado dinámico y stock disponible para cada producto
        $productos->getCollection()->transform(function ($producto) {
            $producto->estado_disponible = $producto->estado_disponible;
            $producto->stock_disponible = $producto->stock_disponible;
            return $producto;
        });

        // Obtener datos para filtros
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filters' => $request->only(['search', 'categoria', 'marca', 'estado'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
            'marcas' => $marcas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar duplicidad PRIMERO: mismo nombre + marca + categoría
        if ($request->filled('nombre') && $request->filled('id_categoria')) {
            $existingProduct = Producto::where('nombre', $request->nombre)
                ->where('id_categoria', $request->id_categoria)
                ->where(function($query) use ($request) {
                    if ($request->filled('id_marca')) {
                        $query->where('id_marca', $request->id_marca);
                    } else {
                        $query->whereNull('id_marca');
                    }
                })
                ->first();

            if ($existingProduct) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '⚠️ Ya existe un producto con el mismo nombre, marca y categoría. No se permiten productos duplicados.',
                        'type' => 'duplicidad'
                    ], 422);
                }
                return back()->withErrors(['duplicidad' => '⚠️ Ya existe un producto con el mismo nombre, marca y categoría. No se permiten productos duplicados.'])->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_marca' => 'nullable|exists:marcas,id_marca',
            'estado' => 'nullable|in:activo,inactivo',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB máximo
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres',
            'precio_venta.required' => 'El precio de venta es obligatorio',
            'precio_venta.numeric' => 'El precio de venta debe ser un número',
            'precio_venta.min' => 'El precio de venta debe ser mayor a 0',
            'id_categoria.required' => 'La categoría es obligatoria',
            'id_categoria.exists' => 'La categoría seleccionada no existe',
            'id_marca.exists' => 'La marca seleccionada no existe',
            'estado.in' => 'El estado debe ser activo o inactivo',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif',
            'imagen.max' => 'La imagen no debe superar los 5MB'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->all();

            // Manejar la subida de imagen
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreArchivo = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('productos', $nombreArchivo, 'public');
                $data['img_url'] = asset(Storage::url($ruta));
            }

            $producto = Producto::create($data);

            return redirect()->route('productos.index')
                ->with('success', 'Producto creado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el producto: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $producto = Producto::with(['categoria', 'marca'])->findOrFail($id);

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
        $producto = Producto::findOrFail($id);

        // Agregar estado dinámico
        $producto->estado_disponible = $producto->estado_disponible;

        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();

        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias,
            'marcas' => $marcas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        // Validar duplicidad PRIMERO: mismo nombre + marca + categoría (excluyendo el producto actual)
        if ($request->has('nombre') && $request->has('id_categoria')) {
            $existingProduct = Producto::where('nombre', $request->nombre)
                ->where('id_categoria', $request->id_categoria)
                ->where(function($query) use ($request) {
                    if ($request->filled('id_marca')) {
                        $query->where('id_marca', $request->id_marca);
                    } else {
                        $query->whereNull('id_marca');
                    }
                })
                ->where('id_producto', '!=', $id) // Excluir el producto actual
                ->first();

            if ($existingProduct) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '⚠️ Ya existe un producto con el mismo nombre, marca y categoría. No se permiten productos duplicados.',
                        'type' => 'duplicidad'
                    ], 422);
                }
                return back()->withErrors(['duplicidad' => '⚠️ Ya existe un producto con el mismo nombre, marca y categoría. No se permiten productos duplicados.'])->withInput();
            }
        }

        // Solo validar campos que se envían
        $rules = [];
        $messages = [];

        if ($request->has('nombre')) {
            $rules['nombre'] = 'required|string|max:50';
            $messages['nombre.required'] = 'El nombre del producto es obligatorio';
            $messages['nombre.max'] = 'El nombre no puede tener más de 50 caracteres';
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

        if ($request->has('id_marca')) {
            $rules['id_marca'] = 'nullable|exists:marcas,id_marca';
            $messages['id_marca.exists'] = 'La marca seleccionada no existe';
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
            $data = $request->only(['nombre', 'descripcion', 'precio_venta', 'id_categoria', 'id_marca', 'estado']);

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
                $data['img_url'] = asset(Storage::url($ruta));
            }

            $producto->update($data);

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado exitosamente');
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

            // Verificar si el producto está disponible (tiene stock)
            if ($producto->estado_disponible === 'disponible') {
                return back()->withErrors([
                    'error' => "No se puede eliminar el producto '{$producto->nombre}' porque está disponible (tiene stock). Primero debe vender todo el stock o ajustar las cantidades."
                ]);
            }

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
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        }
    }
}
