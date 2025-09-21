<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ $titulo ?? 'Sistema' }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            background: white;
        }

        .header {
            display: flex;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .header img {
            height: 40px;
            margin-right: 15px;
        }

        .header .company-info {
            flex: 1;
        }

        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header .company-subtitle {
            font-size: 10px;
            color: #666;
        }

        .header .report-info {
            text-align: right;
        }

        .header .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header .report-date {
            font-size: 9px;
            color: #666;
        }

        .filters {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #495057;
        }

        .filters-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            gap: 5px;
        }

        .filter-label {
            font-weight: bold;
        }

        .content {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        table th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .currency { font-family: 'Courier New', monospace; }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .summary-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
        }

        .summary-value {
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #aaa;
            padding-top: 5px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo -->
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        <div class="company-info">
            <div class="company-name">@yield('company_name', 'Sistema de Gestión')</div>
            <div class="company-subtitle">@yield('company_subtitle', 'Gestión de Inventario y Ventas')</div>
        </div>
        <div class="report-info">
            <div class="report-date">@yield('report_date', now()->format('d/m/Y H:i'))</div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    @hasSection('filters')
    <div class="filters">
        <div class="filters-title">Filtros Aplicados:</div>
        <div class="filters-grid">
            @yield('filters')
        </div>
    </div>
    @endif

    <!-- Contenido principal -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Pie de página -->
    <div class="footer">
        Documento generado por el sistema - {{ config('app.name') }} - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
