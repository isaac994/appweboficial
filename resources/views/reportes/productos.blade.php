@extends('reportes.layout')

@section('title', 'Producto Más Comprado')

@section('content')
<div class="header">
    <h1>📊 Producto Más Comprado</h1>
    <div class="date-info">
        <span>Generado el: {{ date('d/m/Y H:i:s') }}</span>
    </div>
</div>

<!-- Filtros Aplicados -->
<div class="filters-section">
    <h3>🔍 Filtros Aplicados</h3>
    <div class="filters-grid">
        @if(!empty($filtros['fecha_desde']))
            <div class="filter-item">
                <span class="filter-label">Desde:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}</span>
            </div>
        @endif
        @if(!empty($filtros['fecha_hasta']))
            <div class="filter-item">
                <span class="filter-label">Hasta:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</span>
            </div>
        @endif
        <div class="filter-item">
            <span class="filter-label">Tipo:</span>
            <span>Producto más comprado</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Ordenado por:</span>
            <span>{{ $filtros['orden_por'] === 'cantidad' ? 'Cantidad Comprada' : 'Monto Total' }}</span>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="summary">
    <div class="summary-title">📈 Resumen Ejecutivo</div>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-icon">📦</div>
            <div class="summary-content">
                <div class="summary-label">Producto Analizado</div>
                <div class="summary-value">{{ $estadisticas['total_productos'] }}</div>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">📊</div>
            <div class="summary-content">
                <div class="summary-label">Total Cantidad</div>
                <div class="summary-value">{{ number_format($estadisticas['total_cantidad']) }}</div>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">💰</div>
            <div class="summary-content">
                <div class="summary-label">Inversión Total</div>
                <div class="summary-value">Bs {{ number_format($estadisticas['total_monto'], 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">📈</div>
            <div class="summary-content">
                <div class="summary-label">Precio Promedio</div>
                <div class="summary-value">Bs {{ number_format($estadisticas['precio_promedio'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="analysis-box">
        <div class="analysis-header">
            <span class="analysis-icon">💡</span>
            <span>Análisis Ejecutivo</span>
        </div>
        <div class="analysis-content">
            Este reporte muestra el producto más comprado
            @if($filtros['orden_por'] === 'cantidad')
                por cantidad total adquirida
            @else
                por monto total invertido
            @endif
            , representando una inversión total de Bs {{ number_format($estadisticas['total_monto'], 2, ',', '.') }}
            en {{ number_format($estadisticas['total_cantidad']) }} unidades.
        </div>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="table-section">
    <h3>🏆 Producto Más Comprado</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>📱 Producto</th>
                <th>🏷️ Categoría</th>
                <th class="text-center">📊 Cantidad Total</th>
                <th class="text-right">💰 Monto Total</th>
                <th class="text-right">📈 Precio Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $index => $producto)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold; color: #2c3e50;">
                            {{ $producto->producto_nombre }}
                        </div>
                        <div style="color: #666; font-size: 11px; margin-top: 2px;">
                            <strong>Marca:</strong> {{ $producto->marca_nombre }}
                        </div>
                        <div style="color: #666; font-size: 11px;">
                            <strong>Modelo:</strong> {{ $producto->modelo_nombre }}
                        </div>
                        @if($producto->descripcion && !empty(trim($producto->descripcion)))
                            <div style="color: #888; font-size: 10px; font-style: italic; margin-top: 2px;">
                                {{ $producto->descripcion }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 500;">
                            {{ $producto->categoria_nombre }}
                        </span>
                    </td>
                    <td class="text-center font-bold">{{ number_format($producto->total_cantidad) }}</td>
                    <td class="text-right font-bold currency">Bs {{ number_format($producto->total_monto, 2, ',', '.') }}</td>
                    <td class="text-right currency">Bs {{ number_format($producto->precio_promedio, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Pie de página -->
<div class="footer">
    <div class="footer-info">
        <span>Reporte generado automáticamente por el sistema</span>
        <span>Página 1 de 1</span>
    </div>
</div>
@endsection
