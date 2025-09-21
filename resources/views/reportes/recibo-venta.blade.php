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
            border: 2px solid #2c3e50;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 10px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .receipt-number {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .content {
            padding: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ddd;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .products {
            margin: 15px 0;
            border-top: 2px solid #eee;
            padding-top: 10px;
        }

        .product {
            margin-bottom: 12px;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }

        .product-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .product-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .product-price {
            font-weight: bold;
            color: #27ae60;
            font-size: 13px;
        }

        .total {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            border-radius: 4px;
        }

        .operators {
            display: flex;
            justify-content: space-around;
            margin: 10px 0;
            padding: 8px;
            background: #ecf0f1;
            border-radius: 4px;
        }

        .operator {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
        }

        .warranty {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 10px;
            text-align: center;
        }

        .signatures {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .signature {
            text-align: center;
            width: 45%;
        }

        .signature-title {
            font-size: 9px;
            color: #666;
            margin-bottom: 15px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 3px;
        }

        .signature-name {
            font-size: 8px;
            color: #888;
        }

        .footer {
            text-align: center;
            padding: 8px;
            background: #ecf0f1;
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                border: 1px solid #000;
                border-radius: 0;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="receipt-number">Recibo Nº {{ str_pad($venta->id_venta, 4, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="content">
            <!-- Info -->
            @if($venta->cliente)
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $venta->cliente->nombre }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Teléfono:</span>
                <span class="info-value">{{ $venta->cliente->telefono ?? 'N/A' }}</span>
            </div>
            @else
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value">Venta al Público General</span>
            </div>
            @endif

            <!-- Products -->
            <div class="products">
                @foreach($venta->detalles as $detalle)
                <div class="product">
                    <div class="product-name">{{ $detalle->producto->nombre }}</div>
                    <div class="product-line">
                        <span>Cantidad: {{ $detalle->cantidad }}</span>
                        <span class="product-price">Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario, 0) }}</span>
                    </div>
                    @if($detalle->descripcion_dinamica)
                    <div class="product-line">
                        <span>{{ $detalle->descripcion_dinamica }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Total -->
            <div class="total">
                TOTAL: Bs {{ number_format($venta->total, 0) }}
            </div>



        </div>


        <!-- Footer con información del usuario -->
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-top: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 9px; color: #6c757d;">

                <!-- Información del vendedor -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>{{ $usuario->name }}</span>
                    <span>•</span>
                    <span>{{ $usuario->email }}</span>
                </div>

                <!-- Información de contacto -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>67473050</span>
                    <span>•</span>
                    <span>Independencia - Calle Falsa 123</span>
                </div>

                <!-- Fecha -->
                <div>
                    {{ date('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
