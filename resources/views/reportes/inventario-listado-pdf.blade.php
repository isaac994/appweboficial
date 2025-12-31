<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Inventario</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
        .header { margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #333; position: relative; }
        .logo-image { position: absolute; top: 0; left: 0; width: 40px; height: 40px; }
        .header-content { margin-left: 50px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .generado { font-size: 9px; color: #666; margin-top: 2px; }
        .filters { margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ddd; padding: 8px 10px; }
        .filters p { margin: 2px 0; font-size: 9px; }
        .resumen-section { margin-bottom: 12px; }
        .resumen-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .resumen-item { padding: 8px; border: 1px solid #ddd; text-align: center; }
        .resumen-label { font-size: 8px; color: #555; margin-bottom: 3px; font-weight: normal; }
        .resumen-value { font-size: 14px; font-weight: bold; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #e9ecef; border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { border: 1px solid #ddd; padding: 5px 8px; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .currency { font-weight: bold; }
        .empty { text-align: center; color: #666; font-style: italic; padding: 15px; }
    </style>
    <?php use Carbon\Carbon; ?>
    <?php
        $formatCurrency = function($v) {
            $v = floatval($v ?? 0);
            return 'Bs ' . number_format($v, 2, ',', '.');
        };
    ?>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo-image">
        @endif
        <div class="header-content">
            <h1>Listado de Inventario</h1>
            @if(isset($usuario) && $usuario)
            <p style="font-size: 10px; color: #333; margin: 2px 0; font-weight: bold;">{{ trim($usuario->name . ' ' . ($usuario->apellidos ?? '')) }}</p>
            @endif
            <p class="generado">Generado el: {{ $fechaGeneracion }}</p>
        </div>
    </div>

    @if(isset($totales))
    <!-- Resumen de Totales -->
    <div class="resumen-section">
        <h2 style="font-size: 11px; font-weight: bold; margin: 0 0 8px 0; color: #000;">Resumen de Totales</h2>
        <table class="resumen-table">
            <tr>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Total Invertido</div>
                    <div class="resumen-value">{{ $formatCurrency($totales['total_invertido'] ?? 0) }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Total Ingresos</div>
                    <div class="resumen-value">{{ $formatCurrency($totales['total_ingresos'] ?? 0) }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Ganancia Total</div>
                    <div class="resumen-value" style="color: {{ ($totales['ganancia_total'] ?? 0) >= 0 ? '#000' : '#dc3545' }};">{{ ($totales['ganancia_total'] ?? 0) < 0 ? '-' : '' }}{{ $formatCurrency(abs($totales['ganancia_total'] ?? 0)) }}</div>
                </td>
                <td class="resumen-item" style="width: 25%;">
                    <div class="resumen-label">Valor en Stock</div>
                    <div class="resumen-value">{{ $formatCurrency($totales['valor_stock'] ?? 0) }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if(is_array($inventario) && count($inventario) > 0)
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Entradas</th>
                    <th class="text-center">Salidas</th>
                    <th class="text-center">Stock</th>
                    <th class="text-right">Costo Total Entradas</th>
                    <th class="text-right">Ingresos Totales</th>
                    <th class="text-right">Valor Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventario as $item)
                <tr>
                    <td>{{ $item['marca'] }}/{{ $item['modelo'] }} - {{ $item['producto'] }}</td>
                    <td>{{ $item['categoria'] }}</td>
                    <td class="text-center">{{ number_format($item['total_entradas'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item['total_salidas'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item['stock_actual'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right currency">{{ $formatCurrency($item['costo_total_entradas'] ?? 0) }}</td>
                    <td class="text-right currency">{{ $formatCurrency($item['ingresos_totales'] ?? 0) }}</td>
                    <td class="text-right currency">{{ $formatCurrency($item['valor_stock'] ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No hay productos para los filtros seleccionados.</div>
    @endif

</body>
</html>

