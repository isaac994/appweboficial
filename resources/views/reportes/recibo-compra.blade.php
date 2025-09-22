<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Compra #{{ $compra->id_compra }}</title>
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
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo {
            height: 35px;
        }

        .receipt-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
            text-align: center;
            flex: 1;
            margin-top: -10px;
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
            <div class="receipt-title">RECIBO DE COMPRA</div>
        </div>

        <!-- Información de la Compra -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">N° Compra:</span>
                <span>#{{ $compra->id_compra }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span>{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Proveedor:</span>
                <span>{{ $compra->proveedor->nombre }}</span>
            </div>
            @if($compra->proveedor->telefono)
            <div class="info-row">
                <span class="info-label">Teléfono:</span>
                <span>{{ $compra->proveedor->telefono }}</span>
            </div>
            @endif
        </div>

        <!-- Items -->
        <div class="items">
            <div class="items-title">Productos Comprados</div>
            @foreach($compra->detalles as $detalle)
                <div class="item">
                    <div class="item-name">{{ $detalle->producto->nombre }}</div>
                    <div class="item-details">
                        <span>{{ $detalle->cantidad }} x Bs {{ number_format($detalle->precio_unitario, 2, ',', '.') }}</span>
                        <span class="currency">Bs {{ number_format($detalle->total_parcial, 2, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totales -->
        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="currency">Bs {{ number_format($compra->total_calculado, 2, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="currency">Bs {{ number_format($compra->total_calculado, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>¡Compra registrada exitosamente!</p>
            <p>Recibo generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
