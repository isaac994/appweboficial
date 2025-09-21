@extends('reportes.layout')

@section('title', 'Reporte de Compras')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Sistema de Gestión de Compras')
@section('report_title', 'Reporte de Compras')

@section('filters')
    @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']) || !empty($filtros['proveedor']))
        @if(!empty($filtros['fecha_desde']))
            <div class="filter-item">
                <span class="filter-label">Fecha Desde:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}</span>
            </div>
        @endif

        @if(!empty($filtros['fecha_hasta']))
            <div class="filter-item">
                <span class="filter-label">Fecha Hasta:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</span>
            </div>
        @endif

        @if(!empty($filtros['proveedor']))
            <div class="filter-item">
                <span class="filter-label">Proveedor:</span>
                <span>
                    @php
                        $proveedor = \App\Models\Proveedor::find($filtros['proveedor']);
                    @endphp
                    {{ $proveedor ? $proveedor->nombre : 'Proveedor no encontrado' }}
                </span>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($compras->count() > 0)
        <table>
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
                        <tr>
                            @if($index === 0)
                                <td class="text-center font-bold" rowspan="{{ $compra->detalles->count() }}">
                                    #{{ $compra->id_compra }}
                                </td>
                                <td class="text-center" rowspan="{{ $compra->detalles->count() }}">
                                    {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                                </td>
                                <td rowspan="{{ $compra->detalles->count() }}">
                                    {{ $compra->proveedor->nombre }}
                                    @if($compra->proveedor->telefono)
                                        <br><small style="color: #666;">Tel: {{ $compra->proveedor->telefono }}</small>
                                    @endif
                                </td>
                            @endif
                            <td>
                                {{ $detalle->producto->nombre }}
                                @if($detalle->producto->categoria)
                                    <br><small style="color: #666;">{{ $detalle->producto->categoria->nombre }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-right currency">Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            <td class="text-right font-bold currency">Bs {{ number_format($detalle->total_parcial, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f2f2f2; font-weight: bold;">
                    <td colspan="6" class="text-right">TOTAL GENERAL:</td>
                    <td class="text-right currency">Bs {{ number_format($total_general, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
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
