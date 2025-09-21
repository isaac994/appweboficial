<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reporte') - {{ config('app.name', 'Sistema de Gestión') }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #2c3e50;
            background: white;
        }

        /* Header Institucional */
        .institutional-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e74c3c;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .company-subtitle {
            font-size: 11px;
            color: #7f8c8d;
            font-style: italic;
        }

        .report-info {
            text-align: right;
            min-width: 200px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-date {
            font-size: 10px;
            color: #95a5a6;
        }

        /* Filtros y Período */
        .filters-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .filters-title {
            font-size: 11px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 9px;
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .filter-value {
            font-size: 10px;
            color: #495057;
        }

        /* Tablas Profesionales */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .data-table th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 10px 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #34495e;
        }

        .data-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .data-table tbody tr:hover {
            background: #e9ecef;
        }

        /* Estilos para filas especiales */
        .header-row {
            background: #e9ecef !important;
            font-weight: bold;
            color: #495057;
        }

        .detail-row {
            background: #ffffff !important;
        }

        .total-row {
            background: #28a745 !important;
            color: white !important;
            font-weight: bold;
        }

        .total-row td {
            background: #28a745 !important;
            color: white !important;
            border-color: #1e7e34 !important;
        }

        /* Totales y Resúmenes */
        .summary-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            text-align: center;
        }

        .summary-item {
            background: rgba(255,255,255,0.1);
            padding: 8px;
            border-radius: 4px;
        }

        .summary-label {
            font-size: 9px;
            opacity: 0.9;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }

        .currency {
            font-family: 'Courier New', monospace;
            text-align: right;
        }

        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .text-info { color: #17a2b8; }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        /* Sin datos */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px dashed #dee2e6;
        }

        /* Responsive */
        @media print {
            .institutional-header {
                page-break-inside: avoid;
            }

            .data-table {
                page-break-inside: auto;
            }

            .data-table thead {
                display: table-header-group;
            }

            .data-table tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
    <!-- Header Institucional -->
    <div class="institutional-header">
        <div class="logo-section">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            <div class="company-info">
                <div class="company-name">@yield('company_name', 'Sistema de Gestión')</div>
                <div class="company-subtitle">@yield('company_subtitle', 'Gestión de Inventario y Ventas')</div>
            </div>
        </div>
        <div class="report-info">
            <div class="report-title">@yield('report_title', 'Reporte')</div>
            <div class="report-date">@yield('report_date', now()->format('d/m/Y H:i:s'))</div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    @hasSection('filters')
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados</div>
        <div class="filters-grid">
            @yield('filters')
        </div>
    </div>
    @endif

    <!-- Contenido del reporte -->
    @yield('content')

    <!-- Footer -->
    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión</p>
        <p>Página @yield('page_number', '1') - Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
