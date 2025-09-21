<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venta #{{ $venta->id_venta }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            background: white;
            padding: 10px;
        }

        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }

        .header {
            display: flex;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo {
            height: 35px;
            margin-right: 10px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-subtitle {
            font-size: 9px;
            color: #666;
        }

        .receipt-info {
            text-align: right;
        }

        .receipt-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
        }

        .items {
            margin-bottom: 15px;
        }

        .items-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .item {
            margin-bottom: 5px;
            font-size: 10px;
        }

        .item-name {
            font-weight: bold;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            color: #666;
        }

        .item-imei {
            color: #e67e22;
            font-style: italic;
            font-size: 9px;
            margin-top: 2px;
        }

        .totals {
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .total-label {
            font-weight: bold;
        }

        .grand-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9px;
            color: #666;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .currency { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            <div class="company-info">
                <div class="company-name">Tienda de Celulares</div>
                <div class="company-subtitle">Sistema de Gestión de Ventas</div>
            </div>
            <div class="receipt-info">
                <div class="receipt-title">Recibo de Venta</div>
            </div>
        </div>

        <!-- Información de la Venta -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">N° Venta:</span>
                <span>#{{ $venta->id_venta }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span>{{ $venta->cliente->nombre ?? 'Cliente General' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vendedor:</span>
                <span>{{ $venta->usuario->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items">
            <div class="items-title">Productos Vendidos</div>
            @foreach($venta->detalles as $detalle)
                <div class="item">
                    <div class="item-name">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</div>
                    <div class="item-details">
                        <span>{{ $detalle->cantidad }} x Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</span>
                        <span class="currency">Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2, ',', '.') }}</span>
                    </div>
                    @if($detalle->descripcion)
                        <div class="item-imei">IMEI: {{ $detalle->descripcion }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Totales -->
        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="currency">Bs {{ number_format($venta->total, 2, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="currency">Bs {{ number_format($venta->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>¡Gracias por su compra!</p>
            <p>Recibo generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
