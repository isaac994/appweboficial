<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Inventario</title>
    <style>
        @page {
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px;
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

        .filtros {
            margin-bottom: 10px;
            padding: 8px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 9px;
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
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .resumen-label {
            font-size: 8px;
            color: #555;
            margin-bottom: 3px;
            font-weight: normal;
        }

        .resumen-value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8px;
        }

        table th {
            background: #e9ecef;
            color: #000;
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            border: 1px solid #ccc;
        }

        table td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 8px;
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

        .entrada {
            color: #28a745;
            font-weight: bold;
        }

        .salida {
            color: #dc3545;
            font-weight: bold;
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
            <h1>Movimientos de Inventario</h1>
            <p class="generado">Generado el: {{ $fechaGeneracion }}</p>
        </div>
    </div>

    @if(!empty($filtros['tipo']) || !empty($filtros['fecha_hasta']) || !empty($filtros['search']))
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        @if(!empty($filtros['tipo']))
            Tipo: {{ $filtros['tipo'] === 'entrada' ? 'Entradas' : 'Salidas' }}
        @endif
        @if(!empty($filtros['fecha_hasta']))
            {{ !empty($filtros['tipo']) ? ' | ' : '' }}
            Hasta: {{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}
        @endif
        @if(!empty($filtros['search']))
            {{ (!empty($filtros['tipo']) || !empty($filtros['fecha_hasta'])) ? ' | ' : '' }}
            Búsqueda: {{ $filtros['search'] }}
        @endif
    </div>
    @endif

    <!-- Resumen de Totales -->
    <div class="resumen-section">
        <h2 style="font-size: 11px; font-weight: bold; margin: 0 0 8px 0; color: #000;">Resumen de Totales</h2>
        <table class="resumen-table">
            <tr>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Total Entradas</div>
                    <div class="resumen-value">{{ number_format($totales['entradas'], 0, ',', '.') }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Total Salidas</div>
                    <div class="resumen-value">{{ number_format($totales['salidas'], 0, ',', '.') }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Valor Entradas</div>
                    <div class="resumen-value">Bs {{ number_format($totales['valor_entradas'], 2, ',', '.') }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Valor Salidas</div>
                    <div class="resumen-value">Bs {{ number_format($totales['valor_salidas'], 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Movimientos -->
    <div style="margin-top: 10px;">
        <h2 style="font-size: 11px; font-weight: bold; margin: 0 0 8px 0; color: #000;">Detalle de Movimientos</h2>
        @if(count($movimientos) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 7%;">Tipo</th>
                    <th style="width: 15%;">Producto</th>
                    <th style="width: 10%;">Marca</th>
                    <th style="width: 10%;">Categoría</th>
                    <th style="width: 6%;" class="text-center">Cantidad</th>
                    <th style="width: 10%;" class="text-right">Precio Unit.</th>
                    <th style="width: 10%;" class="text-right">Total</th>
                    <th style="width: 10%;">Documento</th>
                    <th style="width: 14%;">Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $movimiento)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movimiento['fecha'])->format('d/m/Y') }}</td>
                    <td class="{{ strtolower($movimiento['tipo']) }}">
                        {{ $movimiento['tipo'] }}
                    </td>
                    <td>{{ $movimiento['producto'] }}</td>
                    <td>{{ $movimiento['marca'] }}</td>
                    <td>{{ $movimiento['categoria'] }}</td>
                    <td class="text-center">{{ number_format($movimiento['cantidad'], 0, ',', '.') }}</td>
                    <td class="text-right currency">Bs {{ number_format($movimiento['precio_unitario'], 2, ',', '.') }}</td>
                    <td class="text-right currency">Bs {{ number_format($movimiento['total'], 2, ',', '.') }}</td>
                    <td>{{ $movimiento['documento'] }}</td>
                    <td>{{ $movimiento['usuario'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-message">No hay movimientos para mostrar con los filtros aplicados</div>
        @endif
    </div>

</body>
</html>


















