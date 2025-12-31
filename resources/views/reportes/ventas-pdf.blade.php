<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Detallado de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            font-size: 12px;
        }

        .header {
            background: transparent;
            color: #000000;
            padding: 5px;
            margin-bottom: 0px;
            text-align: center;
            position: relative;
        }

        .logo-image {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #1a365d;
            text-shadow: none;
            padding-bottom: 0px;
        }

        .header p {
            margin: 2px 0 0 0;
            opacity: 1;
        }

        .periodo {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 2px 0 0px 0;
            text-shadow: none;
        }

        .generado {
            font-size: 14px;
            color: #4a5568;
            margin: 4px 0 0 0;
            text-shadow: none;
        }

        .info-section {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #333;
        }

        .info-value {
            color: #666;
        }

        .venta-section {
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .venta-header {
            background: linear-gradient(135deg, #0a1628 0%, #0d1b2e 100%);
            color: white;
            padding: 15px;
            font-weight: bold;
        }

        .venta-info {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .venta-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .venta-info-item {
            text-align: center;
        }

        .venta-info-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .venta-info-value {
            font-weight: bold;
            color: #333;
        }

        .productos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .productos-table th {
            background: #e9ecef;
            color: #333;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 2px solid #dee2e6;
        }

        .productos-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .productos-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .productos-table tr:hover {
            background-color: #f0f8ff;
        }

        .venta-total {
            background: #e3f2fd;
            padding: 10px 15px;
            text-align: right;
            font-weight: bold;
            color: #1976d2;
            border-top: 2px solid #1976d2;
        }

        .resumen-section {
            background: linear-gradient(135deg, #0a1628 0%, #0d1b2e 100%);
            color: white;
            padding: 5px;
            border-radius: 8px;
            margin-top: 0px;
        }

        .resumen-section::before {
            content: "";
            display: none;
        }

        .resumen-section h3 {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }

        .resumen-item {
            padding: 10px;
        }

        .resumen-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .resumen-label {
            font-size: 12px;
            opacity: 0.9;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 10px 20px;
            border-top: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #333;
        }

        .footer-left {
            text-align: left;
        }

        .footer-center {
            text-align: center;
        }

        .footer-right {
            text-align: right;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo-image">
        <h1>Reporte Ventas</h1>
        <p class="periodo">{{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFinal)) }}</p>
    </div>


    <div class="info-section">
        <h3 style="text-align: center; margin-bottom: 20px;">Detalle Completo de Productos Vendidos</h3>
        <table class="productos-table">
            <thead>
                <tr>
                    <th class="text-center">Nro</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Modelo - Descripción</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unitario</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $contador = 1; @endphp
                @foreach($ventas as $venta)
                    @foreach($venta->detalles_completos as $detalle)
                    <tr>
                        <td class="text-center">{{ $contador++ }}</td>
                        <td>{{ date('d/m/Y', strtotime($venta->fecha)) }}</td>
                        <td>{{ ($detalle->cliente_nombre ?? 'Sin cliente') . ' ' . ($detalle->cliente_apellidos ?? '') }}</td>
                        <td>{{ $detalle->modelo_descripcion ?? 'Sin información' }}</td>
                        <td class="text-center">{{ $detalle->cantidad }}</td>
                        <td class="text-right">Bs. {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                        <td class="text-right font-bold">Bs. {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="info-section">
        <h3 style="text-align: center; margin-bottom: 20px;">Resumen de Ventas por Cliente</h3>
        <table class="productos-table">
            <thead>
                <tr>
                    <th class="text-center">Nro</th>
                    <th>Cliente</th>
                    <th class="text-center">Total Productos</th>
                    <th class="text-right">Total Venta</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $ventasPorCliente = $ventas->groupBy('id_cliente');
                    $contador = 1;
                @endphp
                @foreach($ventasPorCliente as $clienteId => $ventasDelCliente)
                @php
                    $cliente = $ventasDelCliente->first()->cliente;
                    $totalProductos = $ventasDelCliente->sum(function($venta) { return $venta->detalles_completos->sum('cantidad'); });
                    $totalVenta = $ventasDelCliente->sum('total');
                @endphp
                <tr>
                    <td class="text-center">{{ $contador++ }}</td>
                    <td>{{ ($cliente->nombre ?? 'Sin cliente') . ' ' . ($cliente->apellidos ?? '') }}</td>
                    <td class="text-center">{{ $totalProductos }}</td>
                    <td class="text-right font-bold">Bs. {{ number_format($totalVenta, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #e3f2fd; font-weight: bold; border-top: 2px solid #1976d2;">
                    <td colspan="2" class="text-right">TOTALES GENERALES:</td>
                    <td class="text-center">{{ $ventas->sum(function($venta) { return $venta->detalles_completos->sum('cantidad'); }) }}</td>
                    <td class="text-right">Bs. {{ number_format($totalVentas, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="footer-left">{{ $fechaGeneracion }}</div>
        <div class="footer-center">Isaac Mico Serna</div>
        <div class="footer-right">Página 1 de 1</div>
    </div>
</body>
</html>
