<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Factura #{{ $order->order_number }}</title>
    <style>
        /*@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');*/

        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .invoice-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .invoice-number {
            font-size: 16px;
            opacity: 0.9;
        }

        .invoice-date {
            font-size: 14px;
        }

        .company-info {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }

        .company-logo {
            max-height: 50px;
        }

        .client-info {
            padding: 20px 30px;
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-title {
            font-size: 14px;
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: 8px;
        }

        .info-text {
            margin: 4px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f3f4f6;
            font-weight: 500;
            color: #4b5563;
        }

        .items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            padding: 20px 30px;
            background-color: #f8fafc;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-label {
            color: #6b7280;
        }

        .summary-value {
            font-weight: 500;
        }

        .total-row {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 600;
            color: #4f46e5;
        }

        .footer {
            padding: 20px 30px;
            text-align: center;
            color: #9ca3af;
            font-size: 11px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-primary {
            background-color: #e0e7ff;
            color: #4f46e5;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Encabezado -->
        <div class="invoice-header">
            <div>
                <h1 class="invoice-title">Factura</h1>
                <div class="invoice-number">N° {{ $order->order_number }}</div>
            </div>
            <div class="invoice-date">{{ $date }}</div>
        </div>

        <!-- Información de la empresa -->
        <div class="company-info">
            <div>
                <div class="info-title">Matias Eluchans SRL</div>
                <div class="info-text">Sargento Cabral 2005</div>
                <div class="info-text">Ramos Mejía, Buenos Aires</div>
                <div class="info-text">Tel: 111111111</div>
            </div>

            @if(file_exists($logo))

            <img class="company-logo" src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" />

            @endif
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="info-title">Cliente</div>
            <div class="info-text">{{ $order->customer->name }}</div>
            <div class="info-text">{{ $order->customer->document_type }} {{ $order->customer->document_number }}</div>
            <div class="info-text">{{ $order->customer->email }}</div>
            <div class="info-text">{{ $order->customer->phone }}</div>
        </div>

        <!-- Detalles de pago y envío -->
        <div style="padding: 15px 30px; display: flex; gap: 20px; border-bottom: 1px solid #e5e7eb;">
            <div>
                <div class="info-title">Método de pago</div>
                <div class="badge badge-primary">{{ $order->paymentMethod->name ?? 'No especificado' }}</div>
            </div>
            <div>
                <div class="info-title">Estado de pago</div>
                <div class="badge badge-primary">{{ $order->payment_status }}</div>
            </div>
            <div>
                <div class="info-title">Método de envío</div>
                <div class="badge badge-primary">{{ $order->shippingMethod->name ?? 'No especificado' }}</div>
            </div>
        </div>

        <!-- Productos -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Cantidad</th>
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

        <!-- Resumen -->
        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">${{ number_format($order->subtotal, 2, ',', '.') }}</span>
            </div>

            @if($order->shipping_cost > 0)
            <div class="summary-row">
                <span class="summary-label">Envío:</span>
                <span class="summary-value">${{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
            </div>
            @endif

            @if($order->discount > 0)
            <div class="summary-row">
                <span class="summary-label">Descuento:</span>
                <span class="summary-value">-${{ number_format($order->discount, 2, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row total-row">
                <span>Total:</span>
                <span>${{ number_format($order->total_amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <div>Gracias por su compra</div>
            <div style="margin-top: 8px;">Matias Eluchans SRL - CUIT: 30-12345678-9</div>
            <div style="margin-top: 8px;">Factura electrónica válida como comprobante fiscal</div>
        </div>
    </div>
</body>

</html>