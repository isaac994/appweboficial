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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #f5f5f5;
            padding: 10px;
        }

        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            border: 2px solid #e74c3c;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
        }

        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-bottom: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 4px;
            padding: 2px;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-address {
            font-size: 9px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .receipt-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 8px;
        }

        .receipt-info {
            padding: 15px;
            background: white;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            font-size: 10px;
        }

        .info-value {
            color: #34495e;
            font-size: 10px;
        }

        .items {
            margin-top: 15px;
        }

        .items-header {
            background: #f8f9fa;
            padding: 8px;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            color: #2c3e50;
            border: 1px solid #dee2e6;
        }

        .item {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        .item-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 9px;
        }

        .item-imei {
            color: #e67e22;
            font-style: italic;
            font-size: 8px;
            margin-top: 2px;
        }

        .totals {
            background: #f8f9fa;
            padding: 15px;
            border-top: 2px solid #e74c3c;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .total-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .total-value {
            font-weight: bold;
            color: #e74c3c;
        }

        .grand-total {
            border-top: 2px solid #e74c3c;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #e74c3c;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 9px;
        }

        .footer p {
            margin-bottom: 3px;
        }

        .thank-you {
            font-style: italic;
            margin-top: 5px;
            color: #ecf0f1;
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
            <div class="company-name">Tienda de Celulares</div>
            <div class="company-address">Sistema de Gestión de Ventas</div>
            <div class="receipt-title">Recibo de Venta</div>
        </div>

        <!-- Información de la Venta -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">N° Venta:</span>
                <span class="info-value">#{{ $venta->id_venta }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $venta->cliente->nombre ?? 'Cliente General' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vendedor:</span>
                <span class="info-value">{{ $venta->usuario->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items">
            <div class="items-header">PRODUCTOS VENDIDOS</div>
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
                <span class="total-value currency">Bs {{ number_format($venta->total, 2, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="total-value currency">Bs {{ number_format($venta->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>¡Gracias por su compra!</p>
            <p>Recibo generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <div class="thank-you">Sistema de Gestión de Ventas</div>
        </div>
    </div>
</body>
</html>
