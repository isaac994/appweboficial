<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
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
            border: 2px solid #000;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #000;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            background-color: white;
            vertical-align: top;
        }

        tr:hover td {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 15px;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
        }

        .summary h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 14px;
            font-weight: bold;
        }

        .summary p {
            margin: 5px 0;
            font-size: 12px;
            color: #34495e;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
            font-size: 14px;
        }

        .currency {
            text-align: right;
            font-weight: bold;
            color: #27ae60;
        }

        .number {
            text-align: center;
            font-weight: bold;
            color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1 class="title">Reporte de Clientes</h1>
            <p class="subtitle">Lista detallada de clientes registrados en el sistema</p>
            <p class="date">Generado el: {{ $fecha_generacion }}</p>
        </div>
    </div>

    @if($clientes->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Apellidos</th>
                        <th>CI/NIT</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                        <th>Total Ventas</th>
                        <th>Monto Total (Bs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        <tr>
                            <td class="number">#{{ $cliente->id_cliente }}</td>
                            <td><strong>{{ $cliente->nombre }}</strong></td>
                            <td>{{ $cliente->apellidos ?? 'No especificado' }}</td>
                            <td>{{ $cliente->ci ?? 'No especificado' }}</td>
                            <td>{{ $cliente->telefono ?? 'No especificado' }}</td>
                            <td>{{ \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y') }}</td>
                            <td class="number">{{ $cliente->ventas_count ?? 0 }}</td>
                            <td class="currency">{{ number_format($cliente->monto_total ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h3>Resumen del Reporte</h3>
            <p><strong>Total de Clientes:</strong> {{ $clientes->count() }}</p>
            <p><strong>Total de Ventas:</strong> {{ $clientes->sum('ventas_count') }}</p>
            <p><strong>Monto Total Generado:</strong> Bs {{ number_format($clientes->sum('monto_total'), 2) }}</p>
            <p><strong>Promedio de Ventas por Cliente:</strong> {{ $clientes->count() > 0 ? number_format($clientes->sum('monto_total') / $clientes->count(), 2) : '0.00' }} Bs</p>
            <p><strong>Cliente con Mayor Compras:</strong>
                @php
                    $clienteTop = $clientes->sortByDesc('monto_total')->first();
                @endphp
                @if($clienteTop)
                    {{ $clienteTop->nombre }} {{ $clienteTop->apellidos ?? '' }} (Bs {{ number_format($clienteTop->monto_total, 2) }})
                @else
                    No hay datos
                @endif
            </p>
            <p><strong>Moneda:</strong> Bolivianos (Bs)</p>
        </div>
    @else
        <div class="no-data">
            <h3>No hay clientes registrados</h3>
            <p>No se encontraron clientes que coincidan con los criterios de búsqueda.</p>
        </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Sistema de Gestión de Inventarios</p>
        <p>Fecha de generación: {{ $fecha_generacion }}</p>
    </div>
</body>
</html>
