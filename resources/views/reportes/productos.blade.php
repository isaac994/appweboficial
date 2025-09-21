@extends('reportes.layout')

@section('title', 'Reporte de Productos')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Catálogo de Productos e Inventario')
@section('report_title', 'Reporte de Productos')

@section('filters')
    @if(!empty($filtros['categoria']) || !empty($filtros['marca']) || !empty($filtros['stock_minimo']))
        @if(!empty($filtros['categoria']))
            <div class="filter-item">
                <span class="filter-label">Categoría:</span>
                <span>{{ $filtros['categoria'] }}</span>
            </div>
        @endif

        @if(!empty($filtros['marca']))
            <div class="filter-item">
                <span class="filter-label">Marca:</span>
                <span>{{ $filtros['marca'] }}</span>
            </div>
        @endif

        @if(!empty($filtros['stock_minimo']))
            <div class="filter-item">
                <span class="filter-label">Stock Mínimo:</span>
                <span>{{ $filtros['stock_minimo'] }}</span>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($productos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 25%;">Producto</th>
                    <th style="width: 15%;">Categoría</th>
                    <th style="width: 15%;">Marca</th>
                    <th style="width: 12%;">Precio Venta</th>
                    <th style="width: 10%;">Stock</th>
                    <th style="width: 15%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    <tr>
                        <td class="text-center font-bold">#{{ $producto->id_producto }}</td>
                        <td>
                            {{ $producto->nombre }}
                            @if($producto->descripcion)
                                <br><small style="color: #666;">{{ Str::limit($producto->descripcion, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                        <td>{{ $producto->marca->nombre ?? 'Sin marca' }}</td>
                        <td class="text-right currency">Bs {{ number_format($producto->precio_venta, 2, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ $producto->stock_disponible }}</td>
                        <td class="text-center">
                            @if($producto->stock_disponible > 10)
                                <span style="color: #28a745; font-weight: bold;">Disponible</span>
                            @elseif($producto->stock_disponible > 0)
                                <span style="color: #ffc107; font-weight: bold;">Bajo Stock</span>
                            @else
                                <span style="color: #dc3545; font-weight: bold;">Sin Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-title">Resumen de Inventario</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Productos</div>
                    <div class="summary-value">{{ $productos->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Disponibles</div>
                    <div class="summary-value">{{ $productos->where('stock_disponible', '>', 10)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Bajo Stock</div>
                    <div class="summary-value">{{ $productos->where('stock_disponible', '>', 0)->where('stock_disponible', '<=', 10)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Sin Stock</div>
                    <div class="summary-value">{{ $productos->where('stock_disponible', '<=', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Valor Total</div>
                    <div class="summary-value">Bs {{ number_format($productos->sum(function($p) { return $p->stock_disponible * $p->precio_venta; }), 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron productos con los filtros aplicados.</p>
        </div>
    @endif
@endsection
