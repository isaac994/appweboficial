<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        @page {
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            color: #000;
            background: white;
        }

        .header {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
            position: relative;
        }

        .logo-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 40px;
        }

        .header-content {
            margin-left: 50px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #000;
            padding: 0;
        }

        .generado {
            font-size: 9px;
            color: #666;
            margin: 2px 0 0 0;
        }

        .resumen-section {
            margin-bottom: 12px;
        }

        .resumen-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .resumen-item {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .resumen-label {
            font-size: 9px;
            color: #555;
            margin-bottom: 5px;
            font-weight: normal;
        }

        .resumen-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .section {
            margin-bottom: 12px;
            border: 1px solid #ddd;
        }

        .section-title {
            background: #f5f5f5;
            border-bottom: 2px solid #333;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
            color: #000;
            margin: 0;
        }

        .section-content {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table th {
            background: #e9ecef;
            color: #000;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #ccc;
        }

        table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
            color: #000;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .currency {
            font-weight: bold;
            color: #000;
        }

        .empty-message {
            text-align: center;
            padding: 15px;
            color: #666;
            font-style: italic;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo.png')))
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo-image">
        @endif
        <div class="header-content">
            <h1>Reporte de Inventario</h1>
            <p class="generado">Generado el: {{ $fechaGeneracion }}</p>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="resumen-section">
        <h2 style="font-size: 11px; font-weight: bold; margin: 0 0 8px 0; color: #000;">Resumen General</h2>
        <table class="resumen-table">
            <tr>
                <td class="resumen-item" style="width: 50%;">
                    <div class="resumen-label">Valor Total del Inventario</div>
                    <div class="resumen-value">Bs {{ number_format($valor_total_inventario, 2, ',', '.') }}</div>
                </td>
                <td class="resumen-item" style="width: 50%;">
                    <div class="resumen-label">Stock Total de Productos</div>
                    <div class="resumen-value">{{ number_format($total_stock, 0, ',', '.') }} unidades</div>
                </td>
            </tr>
        </table>
    </div>



    <!-- Productos Más Vendidos -->
    <div class="section">
        <div class="section-title">Top 10 Productos Más Vendidos</div>
        <div class="section-content">
            @if($productos_mas_vendidos->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th class="text-center">Unidades Vendidas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos_mas_vendidos as $index => $producto)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $producto['producto'] }}</td>
                        <td>{{ $producto['marca'] }}</td>
                        <td class="text-center">{{ number_format($producto['total_vendido'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-message">No hay datos disponibles</div>
            @endif
        </div>
    </div>

    <!-- Productos con Bajo Stock -->
    <div class="section">
        <div class="section-title">Productos con Bajo Stock</div>
        <div class="section-content">
            @if($productos_bajo_stock->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos_bajo_stock as $producto)
                    <tr>
                        <td>{{ $producto['producto'] }}</td>
                        <td>{{ $producto['marca'] }}</td>
                        <td class="text-center">{{ number_format($producto['stock'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($producto['stock'] == 0)
                            Agotado
                            @else
                            Stock Bajo
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-message">No hay productos con bajo stock</div>
            @endif
        </div>
    </div>

</body>
</html>

