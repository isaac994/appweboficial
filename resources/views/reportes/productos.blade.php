@extends('reportes.layout')

@section('title', 'Reporte de Productos')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Catálogo de Productos e Inventario')
@section('report_title', 'Reporte de Productos')

@section('filters')
    @if(!empty($filtros['categoria']) || !empty($filtros['marca']) || !empty($filtros['stock_minimo']))
        @if(!empty($filtros['categoria']))
            <div class="filter-item">
                <div class="filter-label">Categoría:</div>
                <div class="filter-value">{{ $filtros['categoria'] }}</div>
            </div>
        @endif

        @if(!empty($filtros['marca']))
            <div class="filter-item">
                <div class="filter-label">Marca:</div>
                <div class="filter-value">{{ $filtros['marca'] }}</div>
            </div>
        @endif

        @if(!empty($filtros['stock_minimo']))
            <div class="filter-item">
                <div class="filter-label">Stock Mínimo:</div>
                <div class="filter-value">{{ $filtros['stock_minimo'] }}</div>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($productos->count() > 0)
        <!-- Tabla de Productos -->
        <table class="data-table">
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
                    <tr class="detail-row">
                        <td class="text-center font-bold">#{{ $producto->id_producto }}</td>
                        <td>
                            <div class="font-semibold">{{ $producto->nombre }}</div>
                            @if($producto->descripcion)
                                <div class="text-info" style="font-size: 9px;">{{ Str::limit($producto->descripcion, 50) }}</div>
                            @endif
                        </td>
                        <td>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                        <td>{{ $producto->marca->nombre ?? 'Sin marca' }}</td>
                        <td class="currency">Bs {{ number_format($producto->precio_venta, 2, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="font-bold {{ $producto->stock_disponible > 10 ? 'text-success' : ($producto->stock_disponible > 0 ? 'text-warning' : 'text-danger') }}">
                                {{ $producto->stock_disponible }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($producto->stock_disponible > 10)
                                <span class="text-success font-semibold">Disponible</span>
                            @elseif($producto->stock_disponible > 0)
                                <span class="text-warning font-semibold">Bajo Stock</span>
                            @else
                                <span class="text-danger font-semibold">Sin Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumen de Productos -->
        <div class="summary-section">
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
