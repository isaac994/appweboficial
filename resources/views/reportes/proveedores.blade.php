@extends('reportes.layout')

@section('title', 'Reporte de Proveedores')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Base de Datos de Proveedores')
@section('report_title', 'Reporte de Proveedores')

@section('filters')
    @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']))
        @if($filtros['fecha_desde'] && $filtros['fecha_hasta'])
            <div class="filter-item">
                <span class="filter-label">Período:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</span>
            </div>
        @elseif($filtros['fecha_desde'])
            <div class="filter-item">
                <span class="filter-label">Desde:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}</span>
            </div>
        @elseif($filtros['fecha_hasta'])
            <div class="filter-item">
                <span class="filter-label">Hasta:</span>
                <span>{{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</span>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if($proveedores->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 25%;">Proveedor</th>
                    <th style="width: 15%;">Teléfono</th>
                    <th style="width: 20%;">Dirección</th>
                    <th style="width: 12%;">Total Compras</th>
                    <th style="width: 12%;">Última Compra</th>
                    <th style="width: 8%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proveedores as $proveedor)
                    <tr>
                        <td class="text-center font-bold">#{{ $proveedor->id_proveedor }}</td>
                        <td>
                            {{ $proveedor->nombre }}
                            @if($proveedor->contacto)
                                <br><small style="color: #666;">Contacto: {{ $proveedor->contacto }}</small>
                            @endif
                        </td>
                        <td>{{ $proveedor->telefono ?? 'N/A' }}</td>
                        <td>{{ $proveedor->direccion ?? 'N/A' }}</td>
                        <td class="text-right currency">Bs {{ number_format($proveedor->total_compras ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">
                            @if($proveedor->ultima_compra)
                                {{ \Carbon\Carbon::parse($proveedor->ultima_compra)->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-center">
                            @if($proveedor->total_compras > 0)
                                <span style="color: #28a745; font-weight: bold;">Activo</span>
                            @else
                                <span style="color: #ffc107; font-weight: bold;">Nuevo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-title">Resumen de Proveedores</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Proveedores</div>
                    <div class="summary-value">{{ $proveedores->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Proveedores Activos</div>
                    <div class="summary-value">{{ $proveedores->where('total_compras', '>', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Proveedores Nuevos</div>
                    <div class="summary-value">{{ $proveedores->where('total_compras', '<=', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Compras</div>
                    <div class="summary-value">Bs {{ number_format($proveedores->sum('total_compras'), 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Promedio por Proveedor</div>
                    <div class="summary-value">Bs {{ number_format($proveedores->count() > 0 ? $proveedores->sum('total_compras') / $proveedores->count() : 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron proveedores con los filtros aplicados.</p>
        </div>
    @endif
@endsection
