<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ORDEN DE ENTREGAS</title>
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
        }

        .total-row {
            overflow: hidden;
            margin-bottom: 1mm;
        }

        .total-label {
            float: left;
            width: 40mm;
        }

        .total-value {
            float: right;
            width: 20mm;
            text-align: right;
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
            padding: 1mm 0;
            margin: 2mm 0;
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
                <div class="company-name">Matias Eluchans SRL</div>
                <div class="company-details">
                    <div>Sargento Cabral 2005, Ramos Mejía</div>
                    <div>CUIT: 30-12345678-9</div>
                    <div>Tel: (011) 1234-5678</div>
                </div>
            </div>

            <div class="invoice-info">
                <div class="invoice-title">ORDEN DE PEDIDOS</div>
                <div class="invoice-number"></div>
                <div class="invoice-date">Fecha: {{ $date }}</div>

            </div>
        </div>

        <!-- Información del cliente -->
        <div class="client-info">
            <div class="section-title">INFORME DE PEDIDOS</div>
            <div class="client-name">Fecha de Entrega: {{$dates}}</div>            
        </div>
        
        <!-- Productos -->
        <div class="section-title">DETALLE DE PRODUCTOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60mm;">Productos</th>
                    <th style="width: 20mm;" class="text-right">Bultos</th>                    
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $item)
                <tr>
                    <td>{{ $item->name }}</td>                    
                    <td class="text-right">{{ $item->total_quantity }}</td>                    
                </tr>
                @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-right">{{ $total }}</th>
            </tr>
        </tfoot>
        </table>

        

        
        <!-- Pie de página -->
        
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div>Para consultas: info@empresa.com - Tel: (011) 1234-5678</div>
            <div class="legal-info">
                Conserve este documento para cualquier gestión posterior
            </div>
            
        </div>
        
    </div>
</body>

</html>