<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>REMITO #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 2mm;
            width: 120mm;
        }

        .header {
            text-align: center;
            margin-bottom: 2mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .company-name {
            font-weight: bold;
            font-size: 11px;
        }

        .document-info {
            margin: 3mm 0;
            text-align: center;
            font-weight: bold;
        }

        .client-info {
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #000;
        }

        .client-name {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8px;
        }

        th {
            border-bottom: 1px solid #000;
            padding: 1mm 0;
            text-align: left;
        }

        td {
            padding: 1mm 0;
            border-bottom: 1px dotted #ccc;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-top: 3mm;

            padding-top: 2mm;

            width: 60mm;
            float: right;
            margin-left: 60mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .grand-total {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 1mm 0;
        }

        .footer {
            margin-top: 3mm;
            text-align: center;
            font-size: 7px;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }

        .barcode {
            font-family: 'Libre Barcode 39', monospace;
            font-size: 16px;
            text-align: center;
            margin-top: 2mm;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ $order->tenant->name}}</div>
        <div>{{$order->tenant->address}}</div>
        <div>CUIT: XX-XXXXXXXX-X | Tel: {{$order->tenant->telephone}}</div>
    </div>

    <div class="document-info">
        <div>REMITO N° {{ $order->order_number }}</div>
        <div>Fecha: {{ $date }}</div>
    </div>

    <div class="client-info">
        <div class="client-name">CLIENTE: {{ $order->customer->name ?? 'CONSUMIDOR FINAL' }}</div>
        <div>DIRECCIÓN: {{ $order->customer->address }}</div>
        <div>TEL: {{ $order->customer->telephone }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45mm;">DESCRIPCIÓN</th>
                <th style="width: 10mm;" class="text-right">CANT</th>
                <th style="width: 15mm;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="border-top: 1px dashed #000;"></div>

    <table class="totals">
        <tr>
            <td class="total-row subtotal">
                <span class="total-label">Subtotal:</span>
            </td>
            <td class="total-value subtotal" style="text-align: right;">${{ number_format($order->subtotal, 2, ',', '.') }}</td>
        </tr>

        @if($order->discount > 0)
        <tr>
            <td class="total-row">
                <span class="total-label">Descuento:</span>
            </td>
            <td class="total-value" style="text-align: right;">-${{ number_format($order->discount, 2, ',', '.') }}</td>
        </tr>
        @endif


        <tr>
            <td class="total-row taxes">
                <span class="total-label">IVA (21%):</span>
            </td>
            <td class="total-value" style="text-align: right;">${{ number_format($order->tax_amount, 2, ',', '.') }}</td>

        </tr>


        @if($order->shipping_cost > 0)
        <tr>
            <td class="total-row">
                <span class="total-label">Envío:</span>
            </td>
            <td class="total-value" style="text-align: right;">${{ number_format($order->shipping_cost, 2, ',', '.') }}</td>
        </tr>
        @endif



        <tr class="">
            <td class="grand-total" style="">
                <span class=" total-label">TOTAL:</span>
            </td>
            <td class="grand-total" style="text-align: right">
                ${{ number_format($order->total_amount, 2, ',', '.') }}
            </td>
        </tr>
    </table>
    <div style="margin: 3mm 0; font-size: 8px;">
        <div><strong>ESTADO DE PAGO:</strong> {{ $order->status->name ?? 'No especificado' }}</div>
        <div><strong>ESTADO DE ENVÍO:</strong> {{ $order->shipping ==0 ? 'Sin Envio': 'Con Envio' }}</div>
        @if($order->shipping ==1)
        <div><strong>Fecha Envio:</strong> {{ $order->delivery_date->format('d/m/Y')  }}</div>
        @endif

    </div>

    <div class="footer">
        <div>¡GRACIAS POR SU COMPRA!</div>
        <div>Documento no válido como factura</div>
        <div class="barcode">*{{ $order->order_number }}*</div>
    </div>
</body>

</html>