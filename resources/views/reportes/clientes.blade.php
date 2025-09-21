@extends('reportes.layout')

@section('title', 'Reporte de Clientes')
@section('company_name', 'Tienda de Celulares')
@section('company_subtitle', 'Base de Datos de Clientes')
@section('report_title', 'Reporte de Clientes')

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
    @if($clientes->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 25%;">Cliente</th>
                    <th style="width: 15%;">CI/NIT</th>
                    <th style="width: 15%;">Teléfono</th>
                    <th style="width: 12%;">Total Compras</th>
                    <th style="width: 12%;">Última Compra</th>
                    <th style="width: 13%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                    <tr>
                        <td class="text-center font-bold">#{{ $cliente->id_cliente }}</td>
                        <td>
                            {{ $cliente->nombre }}
                            @if($cliente->apellidos)
                                <br><small style="color: #666;">{{ $cliente->apellidos }}</small>
                            @endif
                        </td>
                        <td>{{ $cliente->ci ?? 'N/A' }}</td>
                        <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                        <td class="text-right currency">Bs {{ number_format($cliente->total_compras ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">
                            @if($cliente->ultima_compra)
                                {{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cliente->total_compras > 0)
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
            <div class="summary-title">Resumen de Clientes</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Clientes</div>
                    <div class="summary-value">{{ $clientes->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Clientes Activos</div>
                    <div class="summary-value">{{ $clientes->where('total_compras', '>', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Clientes Nuevos</div>
                    <div class="summary-value">{{ $clientes->where('total_compras', '<=', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Ventas</div>
                    <div class="summary-value">Bs {{ number_format($clientes->sum('total_compras'), 2, ',', '.') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Promedio por Cliente</div>
                    <div class="summary-value">Bs {{ number_format($clientes->count() > 0 ? $clientes->sum('total_compras') / $clientes->count() : 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron clientes con los filtros aplicados.</p>
        </div>
    @endif
@endsection
