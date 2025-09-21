@extends('reportes.layout')

@section('title', 'Reporte de Compras')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Sistema de Gestión de Compras e Inventario')
@section('report_title', 'Reporte de Compras')

@section('filters')
    @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']) || !empty($filtros['proveedor']))
        @if(!empty($filtros['fecha_desde']))
            <div class="filter-item">
                <div class="filter-label">Fecha Desde:</div>
                <div class="filter-value">{{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}</div>
            </div>
        @endif

        @if(!empty($filtros['fecha_hasta']))
            <div class="filter-item">
                <div class="filter-label">Fecha Hasta:</div>
                <div class="filter-value">{{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</div>
            </div>
        @endif

        @if(!empty($filtros['proveedor']))
            <div class="filter-item">
                <div class="filter-label">Proveedor:</div>
                <div class="filter-value">
                    @php
                        $proveedor = \App\Models\Proveedor::find($filtros['proveedor']);
                    @endphp
                    {{ $proveedor ? $proveedor->nombre : 'Proveedor no encontrado' }}
                </div>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($compras->count() > 0)
        <!-- Tabla de Compras -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">ID Compra</th>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 20%;">Proveedor</th>
                    <th style="width: 30%;">Productos</th>
                    <th style="width: 8%;">Cantidad</th>
                    <th style="width: 12%;">Precio Unit.</th>
                    <th style="width: 12%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compras as $compra)
                    @foreach($compra->detalles as $index => $detalle)
                        <tr class="{{ $index === 0 ? 'header-row' : 'detail-row' }}">
                            @if($index === 0)
                                <td class="text-center font-bold" rowspan="{{ $compra->detalles->count() }}">
                                    #{{ $compra->id_compra }}
                                </td>
                                <td class="text-center" rowspan="{{ $compra->detalles->count() }}">
                                    {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                                </td>
                                <td rowspan="{{ $compra->detalles->count() }}">
                                    <div class="font-semibold">{{ $compra->proveedor->nombre }}</div>
                                    @if($compra->proveedor->telefono)
                                        <div class="text-info" style="font-size: 9px;">Tel: {{ $compra->proveedor->telefono }}</div>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="font-semibold">{{ $detalle->producto->nombre }}</div>
                                @if($detalle->producto->categoria)
                                    <div class="text-info" style="font-size: 9px;">{{ $detalle->producto->categoria->nombre }}</div>
                                @endif
                                @if($detalle->producto->marca)
                                    <div class="text-info" style="font-size: 9px;">{{ $detalle->producto->marca->nombre }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="currency">Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            <td class="currency font-bold">Bs {{ number_format($detalle->total_parcial, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right font-bold">
                        TOTAL GENERAL:
                    </td>
                    <td class="currency font-bold">
                        Bs {{ number_format($total_general, 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Resumen de Compras -->
        <div class="summary-section">
            <div class="summary-title">Resumen de Compras</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Compras</div>
                    <div class="summary-value">{{ $compras->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Monto Total</div>
                    <div class="summary-value">Bs {{ number_format($total_general, 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Proveedores</div>
                    <div class="summary-value">{{ $compras->pluck('proveedor.nombre')->unique()->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Promedio por Compra</div>
                    <div class="summary-value">Bs {{ number_format($compras->count() > 0 ? $total_general / $compras->count() : 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron compras para el período seleccionado.</p>
        </div>
    @endif
@endsection
