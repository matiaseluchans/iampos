<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>REMITO #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;

            /* Ancho más estrecho como en la imagen */
        }

        .border {
            margin-top: 15mm;
            margin-left: 15mm;
            margin-right: 15mm;

            border: 1px solid #000;
        }

        .header {
            text-align: center;
            margin-bottom: 1mm;
            padding-bottom: 1mm;
            border-bottom: 1px solid #000;
        }

        .company-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 1mm;
        }

        .iva-info {
            text-align: center;
            font-size: 8px;
            margin: 1mm 0;
        }

        .document-info {
            margin: 2mm 0;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        .section-title {
            font-weight: bold;
            margin: 2mm 0 1mm 0;
            border-bottom: 1px solid #000;
        }

        .client-info {
            margin-bottom: 2mm;
        }

        .client-row {
            display: flex;
            margin-bottom: 1mm;
        }

        .client-label {
            font-weight: bold;
            width: 20mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1mm 0 2mm 0;
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
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            margin-top: 2mm;
            width: 100%;
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
            margin: 2mm 0;
        }

        .footer {
            margin-top: 2mm;
            text-align: center;
            font-size: 7px;
        }

        .barcode {
            font-family: 'Libre Barcode 39', monospace;
            font-size: 16px;
            text-align: center;
            margin-top: 1mm;
        }

        .observaciones {
            margin-top: 2mm;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="border">
        <div class=" header">
            <table>
                <tr>
                    <td>
                        <div class="company-name">I.V.A RESPONSABLE MONOTRIBUTO</div>
                        <div class="iva-info">Inicio de Actividades: {{ $order->tenant->created_at->format('d-m-Y') ?? '01-03-2012' }}</div>
                    </td>
                    <td>
                        <div class="document-info">
                            <div>REMITO N° {{ $order->order_number }}</div>
                            <div>Fecha: {{ $date }}</div>
                        </div>
                    </td>
                </tr>
            </table>



        </div>

        <div class="section-title">VENDEDOR: <strong>{{ $order->seller_name }}</strong></div>



        <div class="section-title">CLIENTE</div>
        <div style="margin-bottom: 2mm;"><strong>{{ $order->customer->name ?? 'CONSUMIDOR FINAL' }}</strong></div>
        <div class="client-row">
            <span class="client-label">Domicilio:</span>
            <span>{{ $order->customer->address }} - {{ $order->customer->locality ? $order->customer->locality->name :''}}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Teléfono:</span>
            <span>{{ $order->customer->telephone }}</span>
        </div>

        <div class="section-title">OBSERVACIONES:</div>
        <div class="observaciones">{{ $order->notes ?? ' ' }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8mm;">COD</th>
                    <th style="width: 35mm;">PRODUCTO</th>
                    <th style="width: 8mm;" class="text-right">CANT</th>
                    <th style="width: 12mm;" class="text-right">PRECIO </th>
                    <th style="width: 12mm;" class="text-right">MONTO </th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->code }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
                @endforeach

                <tr>
                    <td class="text-right total-row grand-total"> </td>
                    <td class="text-right total-row grand-total">TOTAL</td>
                    <td class="text-right total-row grand-total">{{ $order->items->sum('quantity') }} un</td>
                    <td class="text-right total-row grand-total"> </td>
                    <td class="text-right total-row grand-total">${{ number_format($order->total_amount, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>



        <div class="footer">
            <div>¡GRACIAS POR SU COMPRA!</div>
            <div>Documento no válido como factura</div>
            <div class="barcode">*{{ $order->order_number }}*</div>
        </div>
    </div>

    <div class="border">
        <div class=" header">
            <table>
                <tr>
                    <td>
                        <div class="company-name">I.V.A RESPONSABLE MONOTRIBUTO</div>
                        <div class="iva-info">Inicio de Actividades: {{ $order->tenant->created_at->format('d-m-Y') ?? '01-03-2012' }}</div>
                    </td>
                    <td>
                        <div class="document-info">
                            <div>REMITO N° {{ $order->order_number }}</div>
                            <div>Fecha: {{ $date }}</div>
                        </div>
                    </td>
                </tr>
            </table>



        </div>

        <div class="section-title">VENDEDOR: <strong>{{ $order->seller_name }}</strong></div>



        <div class="section-title">CLIENTE</div>
        <div style="margin-bottom: 2mm;"><strong>{{ $order->customer->name ?? 'CONSUMIDOR FINAL' }}</strong></div>
        <div class="client-row">
            <span class="client-label">Domicilio:</span>
            <span>{{ $order->customer->address }} - {{ $order->customer->locality ? $order->customer->locality->name :''}}</span>
        </div>
        <div class="client-row">
            <span class="client-label">Teléfono:</span>
            <span>{{ $order->customer->telephone }}</span>
        </div>

        <div class="section-title">OBSERVACIONES:</div>
        <div class="observaciones">{{ $order->notes ?? ' ' }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8mm;">COD</th>
                    <th style="width: 35mm;">PRODUCTO</th>
                    <th style="width: 8mm;" class="text-right">CANT</th>
                    <th style="width: 12mm;" class="text-right">PRECIO </th>
                    <th style="width: 12mm;" class="text-right">MONTO </th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->code }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
                @endforeach

                <tr>
                    <td class="text-right total-row grand-total"> </td>
                    <td class="text-right total-row grand-total">TOTAL</td>
                    <td class="text-right total-row grand-total">{{ $order->items->sum('quantity') }} un</td>
                    <td class="text-right total-row grand-total"> </td>
                    <td class="text-right total-row grand-total">${{ number_format($order->total_amount, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>



        <div class="footer">
            <div>¡GRACIAS POR SU COMPRA!</div>
            <div>Documento no válido como factura</div>
            <div class="barcode">*{{ $order->order_number }}*</div>
        </div>
    </div>
</body>


</html>