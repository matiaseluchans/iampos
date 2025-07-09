<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Factura #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 8pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            width: 100%;
            max-width: 100mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .header img {
            max-height: 30mm;
        }

        .company-info {
            font-size: 7pt;
            text-align: center;
            margin-bottom: 5mm;
        }

        .invoice-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2mm;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5mm;
            font-size: 9pt;
        }

        .client-info {
            margin-bottom: 5mm;
            border: 1px solid #eee;
            padding: 3mm;
            font-size: 8pt;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 7pt;
        }

        th {
            background-color: #f5f5f5;
            text-align: left;
            padding: 2mm;
            font-weight: bold;
        }

        td {
            padding: 2mm;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            margin-top: 5mm;
            width: 60%;
            margin-left: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2mm;
        }

        .grand-total {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 2mm;
            font-size: 9pt;
        }

        .footer {
            margin-top: 10mm;
            font-size: 7pt;
            text-align: center;
            color: #777;
        }

        .badge {
            display: inline-block;
            padding: 1mm 2mm;
            border-radius: 2mm;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-primary {
            background-color: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Encabezado -->
        <div class="header">
            @if(file_exists($logo))

            <img style="width:200px" class="company-logo" src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" />

            @endif
            <div class="invoice-title">FACTURA</div>
            <div class="company-info">
                <div>Matias Eluchans SRL</div>
                <div>Sargento Cabral 2005, Ramos Mejía</div>
                <div>CUIT: 30-12345678-9</div>
            </div>
        </div>

        <!-- Detalles de factura -->
        <div class="invoice-details">
            <div>
                <div class="info-label">N° Factura</div>
                <div>{{ $order->order_number }}</div>
            </div>
            <div>
                <div class="info-label">Fecha</div>
                <div>{{ $date }}</div>
            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="info-label">CLIENTE</div>
            <div>{{ $order->customer->name }}</div>
            <div>{{ $order->customer->document_type }}: {{ $order->customer->document_number }}</div>
            <div>Tel: {{ $order->customer->phone }}</div>
        </div>

        <!-- Métodos de pago y envío -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 5mm; font-size: 7pt;">
            <div>
                <div class="info-label">Método de pago</div>
                <div class="badge badge-primary">{{ $order->paymentMethod->name ?? 'No especificado' }}</div>
            </div>
            <div>
                <div class="info-label">Método de envío</div>
                <div class="badge badge-primary">{{ $order->shippingMethod->name ?? 'No especificado' }}</div>
            </div>
        </div>

        <!-- Productos -->
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($order->subtotal, 2, ',', '.') }}</span>
            </div>

            @if($order->shipping_cost > 0)
            <div class="total-row">
                <span>Envío:</span>
                <span>${{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
            </div>
            @endif

            @if($order->discount > 0)
            <div class="total-row">
                <span>Descuento:</span>
                <span>-${{ number_format($order->discount, 2, ',', '.') }}</span>
            </div>
            @endif

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total_amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <div>Gracias por su compra</div>
            <div>Factura electrónica válida como comprobante fiscal</div>
            <div>Conserve este documento para cualquier reclamo</div>
        </div>
    </div>
</body>

</html>