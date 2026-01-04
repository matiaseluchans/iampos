<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        $tenantId = Auth::user()->tenant_id;

        $query = Order::with([
            'customer',
            'items',
            'payment',
            'payment.paymentMethod',
            'paymentStatus',
            'shipmentStatus',
        ])
            ->where('tenant_id', $tenantId);

        // Convertir fechas de Buenos Aires a UTC
        if ($this->startDate && $this->endDate) {
            // Parsear como Buenos Aires y convertir a UTC
            $startDateBA = \Carbon\Carbon::parse($this->startDate . ' 00:00:00', 'America/Argentina/Buenos_Aires');
            $endDateBA = \Carbon\Carbon::parse($this->endDate . ' 23:59:59', 'America/Argentina/Buenos_Aires');

            // Convertir a UTC para comparar con la base de datos
            $startDateUTC = $startDateBA->timezone('UTC');
            $endDateUTC = $endDateBA->timezone('UTC');

            $query->where('created_at', '>=', $startDateUTC)
                ->where('created_at', '<=', $endDateUTC);
        } else if ($this->startDate) {
            $startDateBA = \Carbon\Carbon::parse($this->startDate . ' 00:00:00', 'America/Argentina/Buenos_Aires');
            $startDateUTC = $startDateBA->timezone('UTC');
            $query->where('created_at', '>=', $startDateUTC);
        } else if ($this->endDate) {
            $endDateBA = \Carbon\Carbon::parse($this->endDate . ' 23:59:59', 'America/Argentina/Buenos_Aires');
            $endDateUTC = $endDateBA->timezone('UTC');
            $query->where('created_at', '<=', $endDateUTC);
        } else {
            // Por defecto últimos 30 días desde Buenos Aires
            $thirtyDaysAgoBA = \Carbon\Carbon::now('America/Argentina/Buenos_Aires')->subDays(30)->startOfDay();
            $thirtyDaysAgoUTC = $thirtyDaysAgoBA->timezone('UTC');
            $query->where('created_at', '>=', $thirtyDaysAgoUTC);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID Orden',
            'Cliente Direccion',
            'Cliente Nombre',
            'Fecha',
            'Total',
            'Estado Pago',
            'Estado Envio',
            'Cantidad',
            'Producto',
            'Precio Unitario',
            'Subtotal',


        ];
    }

    public function map($order): array
    {
        $rows = [];

        // Si la orden tiene items, creamos una fila por cada item
        if ($order->items->isNotEmpty()) {
            foreach ($order->items as $item) {
                $rows[] = [
                    $order->order_number,
                    $order->customer->address ?? 'N/A',
                    $order->customer->firstname ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i'),

                    number_format($order->total_amount,  0, '.', ''),

                    $order->paymentStatus->name,
                    $order->shipmentStatus->name,

                    $item->quantity,
                    $item->product->name ?? 'N/A',

                    number_format($item->unit_price, 0, '.', ''),
                    number_format($item->unit_price * $item->quantity, 0, '.', ''),

                ];
            }
        } else {
            // Si no hay items, mostramos solo la orden
            $rows[] = [
                $order->order_number,
                $order->customer->address ?? 'N/A',
                $order->customer->firstname ?? 'N/A',
                $order->created_at->format('Y-m-d H:i'),

                number_format($order->total_amount, 0),

                $order->paymentStatus->name,
                $order->shipmentStatus->name,

                "N/A",
                "N/A",

                "N/A",
                "N/A",

            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '4F81BD']
                ]
            ],
            // Alternar colores en filas
            'A2:O' . ($sheet->getHighestRow()) => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F2F2F2']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID Orden
            'B' => 25, // Cliente
            'C' => 20, // Usuario
            'D' => 15, // Fecha
            'E' => 15, // Estado
            'F' => 20, // Método de Pago
            'G' => 12, // Subtotal
            'H' => 12, // Impuestos
            'I' => 12, // Descuento
            'J' => 12, // Total
            'K' => 30, // Producto
            'L' => 10, // Cantidad
            'M' => 15, // Precio Unitario
            'N' => 15, // Subtotal Item
            'O' => 30, // Notas
        ];
    }

    public function title(): string
    {
        return 'Órdenes';
    }
}
