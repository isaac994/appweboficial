<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #2563eb;
            font-size: 20px;
            margin: 0 0 8px 0;
        }

        .header p {
            color: #666;
            margin: 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #374151;
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

        .low-stock {
            background-color: #fef2f2;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario</h1>
        <p>Estado actual del inventario y valorización</p>
        <p>Generado el: {{ $fechaGeneracion }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Modelo</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th class="text-center">Entradas</th>
                <th class="text-right">Costo Total</th>
                <th class="text-center">Salidas</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Ganancia Total</th>
                <th class="text-right">Ganancia/Unidad</th>
                <th class="text-center">Stock Actual</th>
                <th class="text-right">Valor Stock</th>
                <th class="text-right">Precio Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventario as $item)
            <tr class="{{ $item['stock_actual'] < 5 ? 'low-stock' : '' }}">
                <td>
                    <strong>{{ $item['producto'] }}</strong>
                </td>
                <td>{{ $item['modelo'] }}</td>
                <td>{{ $item['marca'] }}</td>
                <td>{{ $item['categoria'] }}</td>
                <td class="text-center">{{ number_format($item['total_entradas']) }}</td>
                <td class="text-right">Bs {{ number_format($item['costo_total_entradas'], 2) }}</td>
                <td class="text-center">{{ number_format($item['total_salidas']) }}</td>
                <td class="text-right">Bs {{ number_format($item['ingresos_totales'], 2) }}</td>
                <td class="text-right {{ $item['ganancia_bruta'] >= 0 ? 'positive' : 'negative' }}">
                    Bs {{ number_format($item['ganancia_bruta'], 2) }}
                </td>
                <td class="text-right {{ $item['ganancia_por_unidad'] >= 0 ? 'positive' : 'negative' }}">
                    Bs {{ number_format($item['ganancia_por_unidad'], 2) }}
                </td>
                <td class="text-center {{ $item['stock_actual'] < 5 ? 'negative' : '' }}">
                    {{ number_format($item['stock_actual']) }}
                </td>
                <td class="text-right">Bs {{ number_format($item['valor_stock'], 2) }}</td>
                <td class="text-right">Bs {{ number_format($item['precio_venta'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen -->
    <div style="margin-top: 20px; padding: 15px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <h3 style="color: #2563eb; margin: 0 0 10px 0; font-size: 14px;">Resumen del Inventario</h3>
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
            <div style="margin: 5px;">
                <strong>Total Productos:</strong> {{ count($inventario) }}
            </div>
            <div style="margin: 5px;">
                <strong>Valor Total Stock:</strong> Bs {{ number_format(collect($inventario)->sum('valor_stock'), 2) }}
            </div>
            <div style="margin: 5px;">
                <strong>Productos con Stock Bajo:</strong> {{ collect($inventario)->where('stock_actual', '<', 5)->count() }}
            </div>
            <div style="margin: 5px;">
                <strong>Ganancia Total Potencial:</strong> Bs {{ number_format(collect($inventario)->sum('ganancia_potencial_stock'), 2) }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>
</html>
