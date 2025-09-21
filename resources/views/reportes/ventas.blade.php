<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        @page {
            margin: 0.5cm;
            size: A4 landscape;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: white;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
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

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 16px;
            color: #34495e;
            margin-bottom: 15px;
            font-weight: 500;
            font-style: italic;
        }

        .date {
            font-size: 14px;
            color: #95a5a6;
            font-style: italic;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        th {
            background: #2c3e50 !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #34495e;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
            vertical-align: top;
            background: white;
        }

        tr:nth-child(even) td {
            background-color: #f8f9fa;
        }

        tr:nth-child(odd) td {
            background-color: white;
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

        .text-green {
            color: #27ae60;
        }

        .text-red {
            color: #e74c3c;
        }

        .totals {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .totals h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            text-align: center;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }

        .total-item {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 5px;
        }

        .total-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .total-value {
            font-size: 16px;
            font-weight: bold;
        }

        .venta-header {
            background: #ecf0f1 !important;
            font-weight: bold;
            color: #2c3e50;
        }

        .product-detail {
            background: #f8f9fa !important;
            border-left: 3px solid #3498db;
            padding-left: 8px;
        }

        .venta-row {
            background: #e8f4f8 !important;
        }

        .venta-row td {
            background: #e8f4f8 !important;
            border-top: 2px solid #3498db;
        }

        .total-row td {
            border-top: 3px solid #2c3e50 !important;
            font-weight: bold;
            font-size: 12px;
        }

        tfoot {
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
            font-size: 16px;
        }

        .period-info {
            background: #e8f4f8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .period-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 14px;
        }

        .period-info p {
            margin: 0;
            color: #34495e;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1 class="title">Reporte de Ventas</h1>
            <p class="subtitle">Sistema de Gestión de Ventas</p>
            <p class="date">Generado el {{ $fechaGeneracion }}</p>
        </div>
    </div>

    @if($fechaInicio || $fechaFin)
    <div class="period-info">
        <h4>Período del Reporte:</h4>
        <p>
            @if($fechaInicio && $fechaFin)
                Desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            @elseif($fechaInicio)
                Desde {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
            @elseif($fechaFin)
                Hasta {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            @endif
        </p>
    </div>
    @endif

    @if($ventas->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID Venta</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 15%;">Cliente</th>
                        <th style="width: 12%;">Vendedor</th>
                        <th style="width: 20%;">Productos</th>
                        <th style="width: 8%;">Cantidad</th>
                        <th style="width: 10%;">Precio Venta</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 7%;">Ganancia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ventas as $venta)
                        @foreach($venta->detalles as $index => $detalle)
                            <tr class="{{ $index === 0 ? 'venta-row' : '' }}">
                                @if($index === 0)
                                    <td class="text-center font-bold venta-header" rowspan="{{ $venta->detalles->count() }}">
                                        #{{ $venta->id_venta }}
                                    </td>
                                    <td class="text-center venta-header" rowspan="{{ $venta->detalles->count() }}">
                                        {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="venta-header" rowspan="{{ $venta->detalles->count() }}">
                                        {{ $venta->cliente->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="venta-header" rowspan="{{ $venta->detalles->count() }}">
                                        {{ $venta->usuario->name ?? 'N/A' }}
                                    </td>
                                @endif
                                <td class="product-detail">
                                    <strong>{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</strong>
                                    @if($detalle->producto->categoria)
                                        <br><small style="color: #7f8c8d;">{{ $detalle->producto->categoria->nombre }}</small>
                                    @endif
                                    @if($detalle->producto->marca)
                                        <br><small style="color: #7f8c8d;">{{ $detalle->producto->marca->nombre }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $detalle->cantidad }}
                                </td>
                                <td class="text-right">
                                    Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}
                                </td>
                                <td class="text-right font-bold">
                                    Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2, ',', '.') }}
                                </td>
                                @if($index === 0)
                                    <td class="text-right font-bold {{ $venta->ganancia_total >= 0 ? 'text-green' : 'text-red' }}" rowspan="{{ $venta->detalles->count() }}">
                                        Bs {{ number_format($venta->ganancia_total, 2, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="7" class="text-right font-bold" style="background: #2c3e50; color: white; padding: 12px;">
                            TOTALES:
                        </td>
                        <td class="text-right font-bold" style="background: #27ae60; color: white; padding: 12px;">
                            Bs {{ number_format($totalVentas, 2, ',', '.') }}
                        </td>
                        <td class="text-right font-bold {{ $totalGanancia >= 0 ? 'text-green' : 'text-red' }}" style="background: {{ $totalGanancia >= 0 ? '#27ae60' : '#e74c3c' }}; color: white; padding: 12px;">
                            Bs {{ number_format($totalGanancia, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    @else
        <div class="no-data">
            <p>No se encontraron ventas para el período seleccionado.</p>
        </div>
    @endif
</body>
</html>
