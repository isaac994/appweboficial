@extends('reportes.layout')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="header">
    <h1>📊 Reporte de Ventas</h1>
    <div class="date-info">
        <span>Generado el: {{ $fechaGeneracion }}</span>
    </div>
</div>

<!-- Filtros Aplicados -->
<div class="filters-section">
    <h3>🔍 Filtros Aplicados</h3>
    <div class="filters-grid">
        <div class="filter-item">
            <span class="filter-label">Desde:</span>
            <span>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</span>
        </div>
        <div class="filter-item">
            <span class="filter-label">Hasta:</span>
            <span>{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="table-section">
    <h3>📋 Detalle de Ventas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Fecha</th>
                <th>Cliente</th>
                <th>📱 Productos</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
                @foreach($venta->detalles as $detalle)
                    <tr>
                        <td class="text-center font-bold">
                            #{{ $venta->id_venta }}
                        </td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                        </td>
                        <td>
                            {{ ($venta->cliente->nombre ?? 'Sin cliente') . ' ' . ($venta->cliente->apellidos ?? '') }}
                        </td>
                        <td>
                            <div style="font-weight: bold; color: #2c3e50;">
                                {{ $detalle->producto->modelo->nombre ?? 'Sin modelo' }}
                            </div>
                            <div style="color: #666; font-size: 11px; margin-top: 2px;">
                                <strong>Marca:</strong> {{ $detalle->producto->marca->nombre ?? 'Sin marca' }}
                            </div>
                        </td>
                        <td class="text-center">{{ $detalle->cantidad }}</td>
                        <td class="text-right currency">Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                        <td class="text-right font-bold currency">Bs {{ number_format($detalle->total_parcial, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<!-- Resumen Ejecutivo -->
<div class="summary">
    <div class="summary-title">📈 Resumen Ejecutivo</div>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Ventas</div>
            <div class="summary-value">Bs {{ number_format($totalVentas, 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Cantidad de Ventas</div>
            <div class="summary-value">{{ $cantidadVentas }}</div>
        </div>
    </div>
</div>
@endsection





