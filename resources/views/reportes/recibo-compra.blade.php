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
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 10px;
        }

        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            border: 2px solid #333;
        }

        .header {
            background: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-type {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .receipt-number {
            background: white;
            color: #333;
            padding: 5px 12px;
            font-weight: bold;
            font-size: 12px;
        }

        .content {
            padding: 15px;
        }

        .section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            color: #333;
        }

        .products-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            color: #333;
            text-transform: uppercase;
        }

        .product {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 8px;
            border-left: 3px solid #333;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .product-details {
            font-size: 11px;
            color: #666;
        }

        .product-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .product-price {
            font-weight: bold;
            color: #333;
        }

        .total {
            background: #333;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 20px;
        }

        .signature {
            flex: 1;
            text-align: center;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #555;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 20px;
        }

        .signature-name {
            font-size: 9px;
            color: #666;
        }

        .footer {
            text-align: center;
            padding: 10px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }

        @media print {
            body {
                padding: 0;
            }
            .receipt {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">CELULARES Y ACCESORIOS</div>
            <div class="receipt-type">RECIBO DE COMPRA</div>
            <div class="receipt-number">Nº {{ str_pad($compra->id_compra, 4, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="content">
            <!-- Info -->
            <div class="section">
                <div class="info-row">
                    <span class="label">Proveedor:</span>
                    <span class="value">{{ $compra->proveedor->nombre }}</span>
                </div>
                <div class="info-row">
                    <span class="label">CI/NIT:</span>
                    <span class="value">{{ $compra->proveedor->ci_nit ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Teléfono:</span>
                    <span class="value">{{ $compra->proveedor->telefono ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Products -->
            <div class="section">
                <div class="products-title">Productos</div>
                @foreach($compra->detalles as $detalle)
                <div class="product">
                    <div class="product-name">{{ $detalle->producto->nombre }}</div>
                     <div class="product-details">
                         <div class="product-line">
                             <span>Cantidad: {{ $detalle->cantidad }}</span>
                             <span>Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario, 0) }}</span>
                         </div>
                         <div class="product-line">
                             <span>P.Unit: Bs {{ number_format($detalle->precio_unitario, 0) }}</span>
                         </div>
                         @if($detalle->descripcion_dinamica)
                         <div class="product-line">
                             <span>{{ $detalle->descripcion_dinamica }}</span>
                         </div>
                         @endif
                     </div>
                </div>
                @endforeach
            </div>

            <!-- Total -->
            <div class="total">
                TOTAL: Bs {{ number_format($totalCompra, 0) }}
            </div>

        </div>


        <!-- Footer con información del usuario -->
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-top: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 9px; color: #6c757d;">

                <!-- Información del comprador -->
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
