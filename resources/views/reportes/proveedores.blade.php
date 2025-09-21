<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Proveedores</title>
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
            margin: 25px 0;
            border: 2px solid #000;
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            table-layout: fixed;
            border: none;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            text-transform: none;
            letter-spacing: 0;
            border: 1px solid #000;
            border-bottom: 2px solid #000;
        }

        .table th:nth-child(1) { width: 25%; }  /* Proveedor */
        .table th:nth-child(2) { width: 15%; }  /* CI/NIT */
        .table th:nth-child(3) { width: 15%; }  /* Teléfono */
        .table th:nth-child(4) { width: 15%; }  /* Fecha Registro */
        .table th:nth-child(5) { width: 12%; }  /* Total Compras */
        .table th:nth-child(6) { width: 18%; }  /* Monto Total */

        .table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 12px;
            vertical-align: middle;
            background-color: white;
        }

        .table tr:nth-child(even) {
            background-color: white;
        }

        .table tr:nth-child(odd) {
            background-color: white;
        }

        .table tr:hover {
            background-color: #f0f0f0;
        }

        .price {
            font-weight: 600;
            color: #27ae60;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 12px;
        }

        .page-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="title">Reporte de Proveedores</div>
            <div class="subtitle">Listado completo de proveedores registrados en el sistema</div>
            <div class="date">Sistema de Gestión de Inventario - Tienda de Celulares</div>
            <div class="date">Generado el: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>CI/NIT</th>
                    <th>Teléfono</th>
                    <th>Fecha Registro</th>
                    <th>Total Compras</th>
                    <th>Monto Total (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proveedores as $proveedor)
                    <tr>
                        <td><strong>{{ $proveedor->nombre }}</strong></td>
                        <td>{{ $proveedor->ci_nit ?? 'N/A' }}</td>
                        <td>{{ $proveedor->telefono ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($proveedor->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $proveedor->total_compras ?? 0 }}</td>
                        <td class="price">{{ number_format($proveedor->monto_total ?? 0, 2) }} Bs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>📊 Resumen del Reporte:</strong></p>
        <p>• Total de proveedores registrados: <strong>{{ $proveedores->count() }}</strong></p>
        <p>• Reporte generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>• Los montos mostrados están en Bolivianos (Bs)</p>
    </div>

    <div class="page-number">
        Página 1
    </div>
</body>
</html>
