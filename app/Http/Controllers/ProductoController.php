<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
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
        $query = Producto::with(['categoria', 'marca', 'proveedor']);

        // Filtros
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('marca')) {
            $query->where('id_marca', $request->marca);
        }

        $productos = $query->orderBy('nombre')->paginate(6);

        // Obtener datos para filtros
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filters' => $request->only(['search', 'categoria', 'marca'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
            'marcas' => $marcas,
            'proveedores' => $proveedores
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_marca' => 'nullable|exists:marcas,id_marca',
            'id_proveedor' => 'nullable|exists:proveedores,id_proveedor',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB máximo
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 150 caracteres',
            'precio_compra.required' => 'El precio de compra es obligatorio',
            'precio_compra.numeric' => 'El precio de compra debe ser un número',
            'precio_compra.min' => 'El precio de compra debe ser mayor a 0',
            'precio_venta.required' => 'El precio de venta es obligatorio',
            'precio_venta.numeric' => 'El precio de venta debe ser un número',
            'precio_venta.min' => 'El precio de venta debe ser mayor a 0',
            'id_categoria.required' => 'La categoría es obligatoria',
            'id_categoria.exists' => 'La categoría seleccionada no existe',
            'id_marca.exists' => 'La marca seleccionada no existe',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe',
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
                $data['img_url'] = Storage::url($ruta);
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
        $producto = Producto::with(['categoria', 'marca', 'proveedor'])->findOrFail($id);

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
        $categorias = Categoria::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'proveedores' => $proveedores
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        // Solo validar campos que se envían
        $rules = [];
        $messages = [];

        if ($request->has('nombre')) {
            $rules['nombre'] = 'required|string|max:150';
            $messages['nombre.required'] = 'El nombre del producto es obligatorio';
            $messages['nombre.max'] = 'El nombre no puede tener más de 150 caracteres';
        }

        if ($request->has('descripcion')) {
            $rules['descripcion'] = 'nullable|string';
        }

        if ($request->has('precio_compra')) {
            $rules['precio_compra'] = 'required|numeric|min:0';
            $messages['precio_compra.required'] = 'El precio de compra es obligatorio';
            $messages['precio_compra.numeric'] = 'El precio de compra debe ser un número';
            $messages['precio_compra.min'] = 'El precio de compra debe ser mayor a 0';
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

        if ($request->has('id_proveedor')) {
            $rules['id_proveedor'] = 'nullable|exists:proveedores,id_proveedor';
            $messages['id_proveedor.exists'] = 'El proveedor seleccionado no existe';
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
            $data = $request->only(['nombre', 'descripcion', 'precio_compra', 'precio_venta', 'id_categoria', 'id_marca', 'id_proveedor']);

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
                $data['img_url'] = Storage::url($ruta);
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
