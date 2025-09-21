<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
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
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .date {
            font-size: 14px;
            color: #95a5a6;
            font-style: italic;
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

        .producto-row {
            background-color: #fefefe;
            color: #374151;
        }

        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="title">Reporte de Productos</div>
            <div class="date">Sistema de Gestión de Inventario - Tienda de Celulares</div>
            <div class="date">Generado el: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <table class="compras-table">
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 35%;">Nombre del Producto</th>
                <th style="width: 20%;">Marca</th>
                <th style="width: 20%;">Categoría</th>
                <th style="width: 15%;">Precio de Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
                <tr class="producto-row">
                    <td class="text-center font-bold">#{{ $producto->id_producto }}</td>
                    <td><strong>{{ $producto->nombre }}</strong></td>
                    <td>{{ $producto->marca->nombre ?? 'Sin marca' }}</td>
                    <td>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                    <td class="currency font-bold">{{ number_format($producto->precio_venta, 2) }} Bs</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>Total de productos: {{ $productos->count() }}</p>
    </div>

    <div class="page-number">
        Página 1
    </div>
</body>
</html>
