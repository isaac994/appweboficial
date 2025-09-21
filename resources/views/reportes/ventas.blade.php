@extends('reportes.layout')

@section('title', 'Reporte de Ventas')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Sistema de Gestión de Ventas')
@section('report_title', 'Reporte de Ventas')

@section('filters')
    @if($fechaInicio || $fechaFin)
        @if($fechaInicio && $fechaFin)
            <div class="filter-item">
                <span class="filter-label">Período:</span>
                <span>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
            </div>
        @elseif($fechaInicio)
            <div class="filter-item">
                <span class="filter-label">Desde:</span>
                <span>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</span>
            </div>
        @elseif($fechaFin)
            <div class="filter-item">
                <span class="filter-label">Hasta:</span>
                <span>{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</span>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($ventas->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID Venta</th>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 15%;">Cliente</th>
                    <th style="width: 12%;">Vendedor</th>
                    <th style="width: 25%;">Productos</th>
                    <th style="width: 8%;">Cantidad</th>
                    <th style="width: 10%;">Precio Unit.</th>
                    <th style="width: 12%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                    @foreach($venta->detalles as $index => $detalle)
                        <tr>
                            @if($index === 0)
                                <td class="text-center font-bold" rowspan="{{ $venta->detalles->count() }}">
                                    #{{ $venta->id_venta }}
                                </td>
                                <td class="text-center" rowspan="{{ $venta->detalles->count() }}">
                                    {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                                </td>
                                <td rowspan="{{ $venta->detalles->count() }}">
                                    {{ $venta->cliente->nombre ?? 'Cliente General' }}
                                </td>
                                <td rowspan="{{ $venta->detalles->count() }}">
                                    {{ $venta->usuario->name ?? 'N/A' }}
                                </td>
                            @endif
                            <td>
                                {{ $detalle->producto->nombre ?? 'Producto eliminado' }}
                                @if($detalle->producto && $detalle->producto->categoria)
                                    <br><small style="color: #666;">{{ $detalle->producto->categoria->nombre }}</small>
                                @endif
                                @if($detalle->descripcion)
                                    <br><small style="color: #e67e22;">IMEI: {{ $detalle->descripcion }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-right currency">Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            <td class="text-right font-bold currency">Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f2f2f2; font-weight: bold;">
                    <td colspan="7" class="text-right">TOTAL GENERAL:</td>
                    <td class="text-right currency">Bs {{ number_format($totalVentas, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
            <div class="summary-title">Resumen de Ventas</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Ventas</div>
                    <div class="summary-value">{{ $cantidadVentas }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Monto Total</div>
                    <div class="summary-value">Bs {{ number_format($totalVentas, 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Ganancia Total</div>
                    <div class="summary-value">Bs {{ number_format($totalGanancia, 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Promedio por Venta</div>
                    <div class="summary-value">Bs {{ number_format($cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron ventas para el período seleccionado.</p>
        </div>
    @endif
@endsection
