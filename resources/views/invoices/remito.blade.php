<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>REMITO #{{ $order->order_number }}</title>
    <style>
        /* Estilos optimizados para mPDF 150mm */
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.3;

        }

        .invoice-container {
            width: 120mm;
            margin: 0 0;
            padding: 3mm;
            border: #e0e0e0 solid 1px;
        }

        .header {
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 1px solid #e0e0e0;
            overflow: hidden;
        }

        .company-info {
            float: left;
            width: 40%;
        }

        .invoice-info {
            float: right;
            width: 48%;
            font-size: 0.8rem;
            text-align: right;
        }

        .company-name {
            font-size: 0.9rem;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .company-details {
            font-size: 0.7rem;
            color: #555;
        }

        .invoice-title {
            font-size: 0.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1mm;
        }

        .invoice-number {
            font-size: 0.8rem;
            font-weight: bold;
            color: #1976d2;
        }

        .invoice-date {
            font-size: 0.8rem;
            color: #666;
        }

        .logo {
            height: 3mm;
            margin-top: 2mm;
            margin-left: 40%;
        }

        .client-info {
            margin-bottom: 4mm;
            padding: 3mm;
            background-color: #f9f9f9;
            border-left: 3px solid #1976d2;
            font-size: 0.8rem;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 2mm;
            border-bottom: 1px solid #eee;
            padding-bottom: 1mm;
        }

        .client-name {
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .client-details {
            font-size: 0.8rem;
        }

        .detail-item {
            margin-bottom: 1mm;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 20mm;
        }

        .payment-section {
            margin-bottom: 4mm;
            overflow: hidden;
            background-color: #f5f9ff;
        }

        .payment-method {
            width: 40%;
            float: left;
            padding: 2mm;
            background-color: #f5f9ff;
            font-size: 0.9rem;
        }

        .shipping-method {
            width: 48%;
            float: right;
            padding: 2mm;
            background-color: #f5f9ff;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 0.9rem;
        }

        th {
            background-color: #1976d2;
            color: white;
            padding: 2mm;
            font-weight: bold;
            text-align: left;
        }

        td {
            padding: 1mm 2mm;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .product-code {
            color: #666;
            font-size: 0.7rem;
        }

        .totals {
            width: 60mm;
            float: right;
            margin-top: 3mm;
            margin-left: 60mm;
        }

        .total-row {
            overflow: hidden;
            margin-bottom: 1mm;
            border-bottom: 0px;
        }

        .total-label {
            float: left;
            width: 40mm;
        }

        .total-value {
            float: right;
            width: 20mm;
            text-align: right;
            border-bottom: 0px;
        }

        .subtotal {
            border-top: 1px solid #ddd;
            padding-top: 2mm;
        }

        .taxes {
            color: #666;
        }

        .grand-total {
            font-weight: bold;
            border-top: 1px solid #1976d2;
            border-bottom: 1px solid #1976d2;
            padding: 2mm 0;
            margin: 3mm 0;
        }

        .notes {
            margin-top: 4mm;
            padding: 2mm;
            background-color: #f9f9f9;
            font-size: 0.8rem;
            clear: both;
        }

        .footer {
            margin-top: 4mm;
            font-size: 0.7rem;
            text-align: center;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 2mm;
        }

        .legal-info {
            font-size: 0.7rem;
            color: #999;
            margin-top: 1mm;
        }

        .barcode {
            margin-top: 3mm;
            text-align: center;
            font-family: 'idautomationhc39m', monospace;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Encabezado -->
        <div class="header">
            @if(file_exists($logo))
            <img class="logo" src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" />
            @endif
            <div class="company-info">
                <div class="company-name">{{$order->tenant->name}}</div>
                <div class="company-details">
                    <div>{{$order->tenant->address}}</div>
                    <div>CUIT: XX-XXXXXXXX-X</div>
                    <div>Tel: {{$order->tenant->telephone}}</div>
                </div>
            </div>

            <div class="invoice-info">
                <div class="invoice-title">REMITO</div>
                <div class="invoice-number">N° {{ $order->order_number }}</div>
                <div class="invoice-date">Fecha: {{ $date }}</div>

            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="section-title">DATOS DEL CLIENTE</div>
            <div class="client-name">{{ $order->customer->name ?? 'Consumidor Final' }}</div>
            <div class="client-details">
                <div class="detail-item"><span class="detail-label">Dirección:</span> {{ $order->customer->address }}</div>
                <div class="detail-item"><span class="detail-label">Teléfono:</span> {{ $order->customer->telephone }}</div>
            </div>
        </div>

        <!-- Métodos de pago y envío -->
        <div class="payment-section">
            <div class="payment-method">
                <div class="section-title">ESTADO DE PAGO</div>
                <div class="detail-item"><span class="detail-label">Estado:</span> {{ $order->status->name ?? 'No especificado' }}</div>

            </div>

            <div class="shipping-method">
                <div class="section-title">ESTADO ENVÍO</div>
                <div class="detail-item"><span class="detail-label">Estado:</span> {{ $order->shipping ==0 ? 'Sin Envio': 'Con Envio' }}</div>
                @if($order->shipping ==1)
                <div class="detail-item"><span class="detail-label">Fecha Envio:</span>{{ $order->delivery_date->format('d/m/Y')  }}</div>
                @endif
            </div>
        </div>

        <!-- Productos -->
        <div class="section-title">DETALLE DE PRODUCTOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60mm;">Descripción</th>
                    <th style="width: 20mm;" class="text-right">P. Unit.</th>
                    <th style="width: 15mm;" class="text-right">Cant.</th>
                    <th style="width: 25mm;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}<br><span class="product-code">Código: {{ $item->product->code }}</span></td>
                    <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <table class="totals">
            <tr>
                <td class="total-row subtotal">
                    <span class="total-label">Subtotal:</span>
                </td>
                <td class="total-value subtotal">${{ number_format($order->subtotal, 2, ',', '.') }}</td>
            </tr>

            @if($order->discount > 0)
            <tr>
                <td class="total-row">
                    <span class="total-label">Descuento:</span>
                </td>
                <td class="total-value">-${{ number_format($order->discount, 2, ',', '.') }}</td>
            </tr>
            @endif


            <tr>
                <td class="total-row taxes">
                    <span class="total-label">IVA (21%):</span>
                </td>
                <td class="total-value">${{ number_format($order->tax_amount, 2, ',', '.') }}</td>

            </tr>


            @if($order->shipping_cost > 0)
            <tr>
                <td class="total-row">
                    <span class="total-label">Envío:</span>
                </td>
                <td class="total-value">${{ number_format($order->shipping_cost, 2, ',', '.') }}</td>
            </tr>
            @endif



            <tr class="">
                <td class="grand-total" style="padding-left: 2mm;">
                    <span class=" total-label">TOTAL:</span>
                </td>
                <td class="grand-total" style="text-align: right;padding-right:2mm">
                    ${{ number_format($order->total_amount, 2, ',', '.') }}
                </td>
            </tr>
        </table>




        <!-- Notas -->
        <div class="notes">
            <div class="section-title">INFORMACIÓN ADICIONAL</div>
            <div>{{ $order->notes ?? 'Sin observaciones' }}</div>
            <div class="legal-info">
                Documento no válido como comprobante fiscal según RG AFIP 4291/2018
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div>Para consultas: info@empresa.com - Tel: (011) 1234-5678</div>
            <div class="legal-info">
                Conserve este documento para cualquier gestión posterior
            </div>
            <div class="barcode">*{{ $order->order_number }}*</div>
        </div>
    </div>
</body>

</html>