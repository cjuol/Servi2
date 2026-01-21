<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm">
    <title>Ticket #{{ $order->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .logo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.3;
        }

        .ticket-info {
            margin: 10px 0;
            font-size: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .ticket-info div {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .items-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        .items-table thead {
            border-bottom: 1px solid #000;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
            padding: 5px 0;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px 0;
            font-size: 11px;
        }

        .items-table .qty {
            width: 15%;
            text-align: center;
        }

        .items-table .product {
            width: 50%;
        }

        .items-table .price {
            width: 35%;
            text-align: right;
        }

        .totals {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            font-size: 11px;
        }

        .totals div {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .totals .grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }

        .payment-info {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .payment-info div {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            font-size: 10px;
        }

        .footer p {
            margin: 3px 0;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- CABECERA -->
    <div class="header">
        <div class="logo">{{ config('app.name', 'MI RESTAURANTE') }}</div>
        <div class="company-info">
            <div>Calle Ejemplo, 123</div>
            <div>28080 Madrid, España</div>
            <div>Tel: +34 912 345 678</div>
            <div>NIF: B12345678</div>
        </div>
    </div>

    <!-- INFORMACIÓN DEL TICKET -->
    <div class="ticket-info">
        <div>
            <span><strong>TICKET:</strong></span>
            <span>#{{ $order->ticket_number }}</span>
        </div>
        <div>
            <span><strong>FECHA:</strong></span>
            <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div>
            <span><strong>CAJERO:</strong></span>
            <span>{{ $order->user->name ?? 'N/A' }}</span>
        </div>
        @if($order->restaurant_table_id)
        <div>
            <span><strong>MESA:</strong></span>
            <span>{{ $order->restaurantTable->name ?? 'N/A' }}</span>
        </div>
        @endif
    </div>

    <!-- PRODUCTOS -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="qty">CANT</th>
                <th class="product">PRODUCTO</th>
                <th class="price">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="product">{{ $item->product->name ?? 'Producto' }}</td>
                <td class="price">{{ number_format($item->subtotal / 100, 2, ',', '.') }} €</td>
            </tr>
            @if($item->tax_rate > 0)
            <tr>
                <td></td>
                <td colspan="2" style="font-size: 9px; color: #666;">
                    (IVA {{ $item->tax_rate }}%: {{ number_format(($item->subtotal * $item->tax_rate / 100) / 100, 2, ',', '.') }} €)
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <div class="totals">
        @php
            $subtotal = $order->items->sum('subtotal');
            $totalTax = $order->items->sum(function($item) {
                return $item->subtotal * $item->tax_rate / 100;
            });
            $tip = $order->tip ?? 0;
            $totalWithoutTip = $subtotal + $totalTax;
        @endphp
        
        <div>
            <span>SUBTOTAL:</span>
            <span>{{ number_format($subtotal / 100, 2, ',', '.') }} €</span>
        </div>
        <div>
            <span>IVA:</span>
            <span>{{ number_format($totalTax / 100, 2, ',', '.') }} €</span>
        </div>
        @if($tip > 0)
        <div style="color: #16a34a;">
            <span>PROPINA:</span>
            <span>{{ number_format($tip / 100, 2, ',', '.') }} €</span>
        </div>
        @endif
        <div class="grand-total">
            <span>TOTAL:</span>
            <span>{{ number_format($order->total / 100, 2, ',', '.') }} €</span>
        </div>
    </div>

    <!-- INFORMACIÓN DE PAGO -->
    <div class="payment-info">
        <div>
            <span><strong>MÉTODO DE PAGO:</strong></span>
            <span>{{ $order->payment_method->getLabel() }}</span>
        </div>
        @if($order->stripe_payment_id)
        <div>
            <span>ID Stripe:</span>
            <span style="font-size: 8px;">{{ Str::limit($order->stripe_payment_id, 20) }}</span>
        </div>
        @endif
        <div>
            <span><strong>ESTADO:</strong></span>
            <span>{{ $order->status->getLabel() }}</span>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p>*** GRACIAS POR SU VISITA ***</p>
        <p>¡Esperamos volver a verle pronto!</p>
        <p style="margin-top: 8px; font-size: 9px;">
            www.mirestaurante.com
        </p>
    </div>

    <!-- AUTO-PRINT SCRIPT -->
    <script>
        window.onload = function() {
            // Pequeño delay para asegurar que todo esté cargado
            //setTimeout(function() {
            //    window.print();
            //    
            //    // Cerrar la ventana después de imprimir (solo funciona si se abrió con window.open)
            //    setTimeout(function() {
            //        window.close();
            //    }, 500);
            //}, 250);
        };
    </script>
</body>
</html>
