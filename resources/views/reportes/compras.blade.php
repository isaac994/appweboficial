<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compras</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            gap: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .header-content {
            text-align: center;
        }

        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        .filters {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .filters h3 {
            color: #374151;
            font-size: 14px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-item {
            flex: 1;
            min-width: 150px;
        }

        .filter-label {
            font-weight: bold;
            color: #4b5563;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .filter-value {
            color: #6b7280;
            font-size: 11px;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .stat-card {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            flex: 1;
            min-width: 120px;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .compras-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .compras-table th {
            background-color: #2563eb;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #1d4ed8;
        }

        .compras-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
            vertical-align: top;
        }

        .compras-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .compras-table tr:hover {
            background-color: #f3f4f6;
        }

        .compra-header {
            background-color: #e0f2fe;
            font-weight: bold;
            color: #0369a1;
        }

        .producto-row {
            background-color: #fefefe;
            color: #374151;
        }

        .total-row {
            background-color: #dcfce7;
            font-weight: bold;
            color: #166534;
        }

        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .page-break {
            page-break-before: always;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-sm {
            font-size: 10px;
        }

        .text-xs {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1>Reporte de Compras</h1>
            <p>Sistema de Gestión de Inventario - Tienda de Celulares</p>
            <p>Generado el: {{ $fecha_generacion }}</p>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']) || !empty($filtros['proveedor']))
    <div class="filters">
        <h3>Filtros Aplicados</h3>
        <div class="filter-row">
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
        </div>
    </div>
    @endif

    <!-- Tabla de Compras -->
    <table class="compras-table">
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 20%;">Proveedor</th>
                <th style="width: 30%;">Productos</th>
                <th style="width: 10%;">Cantidad</th>
                <th style="width: 10%;">Precio Unit.</th>
                <th style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $compra)
                @foreach($compra->detalles as $index => $detalle)
                    <tr class="{{ $index === 0 ? 'compra-header' : 'producto-row' }}">
                        @if($index === 0)
                            <td rowspan="{{ $compra->detalles->count() }}" class="text-center font-bold">
                                #{{ $compra->id_compra }}
                            </td>
                            <td rowspan="{{ $compra->detalles->count() }}" class="text-center">
                                {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                            </td>
                            <td rowspan="{{ $compra->detalles->count() }}">
                                {{ $compra->proveedor->nombre }}
                                @if($compra->proveedor->telefono)
                                    <br><span class="text-xs">Tel: {{ $compra->proveedor->telefono }}</span>
                                @endif
                            </td>
                        @endif
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td class="text-center">{{ $detalle->cantidad }}</td>
                        <td class="currency">{{ number_format($detalle->precio_unitario, 2) }} Bs</td>
                        <td class="currency font-bold">{{ number_format($detalle->total_parcial, 2) }} Bs</td>
                    </tr>
                @endforeach

                <!-- Espacio entre compras -->
                <tr>
                    <td colspan="7" style="height: 10px; border: none;"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales por Compra -->
    <div style="margin-top: 20px;">
        <h3 style="color: #374151; margin: 0 0 15px 0; font-size: 16px; font-weight: bold;">Totales por Compra</h3>
        <table class="compras-table" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th style="width: 15%;">ID Compra</th>
                    <th style="width: 20%;">Fecha</th>
                    <th style="width: 35%;">Proveedor</th>
                    <th style="width: 30%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compras as $compra)
                    <tr>
                        <td class="text-center font-bold">#{{ $compra->id_compra }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $compra->proveedor->nombre }}</td>
                        <td class="currency font-bold">{{ number_format($compra->total_calculado, 2) }} Bs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Resumen Final -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;">
        <h3 style="color: #0369a1; margin: 0 0 10px 0; font-size: 14px;">Resumen General</h3>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Total de Compras:</strong> {{ $compras->count() }}<br>
                <strong>Proveedores Únicos:</strong> {{ $compras->pluck('proveedor.nombre')->unique()->count() }}
            </div>
            <div style="text-align: right;">
                <div style="font-size: 18px; font-weight: bold; color: #166534;">
                    TOTAL GENERAL: {{ number_format($total_general, 2) }} Bs

                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>Fecha de generación: {{ $fecha_generacion }}</p>
    </div>
</body>
</html>
