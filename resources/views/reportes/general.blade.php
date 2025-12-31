<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General de Negocio</title>
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
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 10px 0;
        }

        .header p {
            color: #666;
            margin: 0;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .summary-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 5px;
            flex: 1;
            min-width: 200px;
            text-align: center;
        }

        .summary-item h3 {
            color: #2563eb;
            font-size: 14px;
            margin: 0 0 8px 0;
        }

        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin: 0;
        }

        .summary-item .subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 5px 0 0 0;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #1e40af;
            font-size: 16px;
            margin: 0 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #374151;
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .positive {
            color: #059669;
            font-weight: bold;
        }

        .negative {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte General de Negocio</h1>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p>Generado el: {{ $fechaGeneracion }}</p>
    </div>

    <!-- Resumen General -->
    <div class="summary">
        <div class="summary-item">
            <h3>Total Compras</h3>
            <p class="value">Bs {{ number_format($datos['totales']['total_compras'], 2) }}</p>
            <p class="subtitle">{{ $datos['totales']['numero_compras'] }} transacciones</p>
            <p class="subtitle">Promedio: Bs {{ number_format($datos['totales']['promedio_compra'], 2) }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Ventas</h3>
            <p class="value">Bs {{ number_format($datos['totales']['total_ventas'], 2) }}</p>
            <p class="subtitle">{{ $datos['totales']['numero_ventas'] }} transacciones</p>
            <p class="subtitle">Promedio: Bs {{ number_format($datos['totales']['promedio_venta'], 2) }}</p>
        </div>
        <div class="summary-item">
            <h3>Ganancias</h3>
            <p class="value {{ $datos['totales']['ganancias'] >= 0 ? 'positive' : 'negative' }}">
                Bs {{ number_format($datos['totales']['ganancias'], 2) }}
            </p>
            <p class="subtitle">{{ $datos['totales']['margen_ganancia'] }}% margen</p>
            <p class="subtitle">Rotación: {{ $datos['totales']['rotacion_inventario'] }}x</p>
        </div>
        <div class="summary-item">
            <h3>Productos Vendidos</h3>
            <p class="value">{{ number_format($datos['totales']['total_productos_vendidos']) }}</p>
            <p class="subtitle">unidades</p>
            <p class="subtitle">{{ $datos['totales']['dias_inventario'] }} días inventario</p>
        </div>
    </div>

    <!-- Análisis de Tendencias -->
    @if(isset($datos['tendencias']))
    <div class="section">
        <h2>Análisis de Tendencias</h2>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="text-align: center; flex: 1;">
                <h4>Compras</h4>
                <p style="font-size: 18px; font-weight: bold; color: {{ $datos['tendencias']['tendencia_compras'] === 'Crecimiento' ? '#059669' : ($datos['tendencias']['tendencia_compras'] === 'Decrecimiento' ? '#dc2626' : '#6b7280') }};">
                    {{ $datos['tendencias']['tendencia_compras'] }}
                </p>
                <p style="font-size: 12px; color: #666;">{{ $datos['tendencias']['crecimiento_promedio_compras'] }}% promedio</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <h4>Ventas</h4>
                <p style="font-size: 18px; font-weight: bold; color: {{ $datos['tendencias']['tendencia_ventas'] === 'Crecimiento' ? '#059669' : ($datos['tendencias']['tendencia_ventas'] === 'Decrecimiento' ? '#dc2626' : '#6b7280') }};">
                    {{ $datos['tendencias']['tendencia_ventas'] }}
                </p>
                <p style="font-size: 12px; color: #666;">{{ $datos['tendencias']['crecimiento_promedio_ventas'] }}% promedio</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <h4>Ganancias</h4>
                <p style="font-size: 18px; font-weight: bold; color: {{ $datos['tendencias']['tendencia_ganancias'] === 'Crecimiento' ? '#059669' : ($datos['tendencias']['tendencia_ganancias'] === 'Decrecimiento' ? '#dc2626' : '#6b7280') }};">
                    {{ $datos['tendencias']['tendencia_ganancias'] }}
                </p>
                <p style="font-size: 12px; color: #666;">{{ $datos['tendencias']['crecimiento_promedio_ganancias'] }}% promedio</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Métricas de Rendimiento -->
    @if(isset($datos['metricas_rendimiento']))
    <div class="section">
        <h2>Métricas de Rendimiento</h2>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="text-align: center; flex: 1;">
                <h4>Ventas por Día</h4>
                <p style="font-size: 16px; font-weight: bold;">Bs {{ number_format($datos['metricas_rendimiento']['ventas_por_dia'], 2) }}</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <h4>Compras por Día</h4>
                <p style="font-size: 16px; font-weight: bold;">Bs {{ number_format($datos['metricas_rendimiento']['compras_por_dia'], 2) }}</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <h4>Productos Únicos</h4>
                <p style="font-size: 16px; font-weight: bold;">{{ $datos['metricas_rendimiento']['productos_unicos_vendidos'] }}</p>
            </div>
            <div style="text-align: center; flex: 1;">
                <h4>Transacciones/Día</h4>
                <p style="font-size: 16px; font-weight: bold;">{{ $datos['metricas_rendimiento']['promedio_transacciones_dia'] }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Productos Más Vendidos -->
    @if(count($datos['productos_mas_vendidos']) > 0)
    <div class="section">
        <h2>Productos Más Vendidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad Vendida</th>
                    <th class="text-right">Ingresos</th>
                    <th class="text-right">Ganancia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['productos_mas_vendidos'] as $producto)
                <tr>
                    <td>
                        <strong>{{ $producto->nombre }}</strong><br>
                        <small>{{ $producto->categoria }} - {{ $producto->marca }}</small>
                    </td>
                    <td class="text-center">{{ number_format($producto->cantidad_vendida) }}</td>
                    <td class="text-right">Bs {{ number_format($producto->ingresos, 2) }}</td>
                    <td class="text-right {{ $producto->ganancia >= 0 ? 'positive' : 'negative' }}">
                        Bs {{ number_format($producto->ganancia, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Análisis por Período -->
    @if(count($datos['analisis_periodo']) > 0)
    <div class="section">
        <h2>Análisis por Período</h2>
        <table>
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="text-right">Compras</th>
                    <th class="text-right">Ventas</th>
                    <th class="text-right">Ganancias</th>
                    <th class="text-center">Transacciones</th>
                    <th class="text-right">Prom. Compra</th>
                    <th class="text-right">Prom. Venta</th>
                    <th class="text-center">Margen %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['analisis_periodo'] as $periodo)
                <tr>
                    <td><strong>{{ $periodo['periodo'] }}</strong></td>
                    <td class="text-right">Bs {{ number_format($periodo['compras'], 2) }}</td>
                    <td class="text-right">Bs {{ number_format($periodo['ventas'], 2) }}</td>
                    <td class="text-right {{ $periodo['ganancias'] >= 0 ? 'positive' : 'negative' }}">
                        Bs {{ number_format($periodo['ganancias'], 2) }}
                    </td>
                    <td class="text-center">{{ $periodo['transacciones'] }}</td>
                    <td class="text-right">Bs {{ number_format($periodo['promedio_compra'], 2) }}</td>
                    <td class="text-right">Bs {{ number_format($periodo['promedio_venta'], 2) }}</td>
                    <td class="text-center {{ $periodo['margen_mes'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $periodo['margen_mes'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>
</html>
