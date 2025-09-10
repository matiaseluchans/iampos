<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeliveryReportExcelExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $orders;
    protected $dates;
    protected $totalQuantity;
    protected $user;

    public function __construct($orders, $dates, $totalQuantity, $user)
    {
        $this->orders = $orders;
        $this->dates = $dates;
        $this->totalQuantity = $totalQuantity;
        $this->user = $user;
    }

    public function collection()
    {
        $data = $this->orders->map(function ($order, $index) {
            return [
                /*'N°' => $index + 1,
                'ID Producto' => $order->product_id,*/
                'Producto' => $order->product_name,
                'Cantidad' => $order->total_quantity
            ];
        });

        // Agregar fila de totales
        $data->push([
            //'N°' => 'TOTAL',
            //'ID Producto' => '',
            //'Producto' => '',
            'Producto' => 'TOTAL',
            'Cantidad' => $this->totalQuantity
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            ['Reporte de Productos para Entrega'],
            ['Generado por: ' . $this->user->name],
            [$this->dates ? 'Período: ' . $this->dates : 'Todos los períodos'],
            ['Generado el: ' . now()->format('d/m/Y H:i:s')],
            [],
            ['Producto', 'Cantidad']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el título principal
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => 'left']
            ],

            // Estilo para información del usuario
            2 => ['font' => ['italic' => true]],

            // Estilo para el período
            3 => ['font' => ['italic' => true]],

            // Estilo para la fecha de generación
            4 => ['font' => ['italic' => true]],

            // Estilo para los encabezados de columna
            6 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E6E6FA']]
            ],

            // Estilo para la fila de totales (última fila)
            count($this->orders) + 7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFACD']]
            ],

            // Alinear números a la derecha
            'B' => ['alignment' => ['horizontal' => 'right']],

            // Alinear encabezados al centro
            'A6:B6' => ['alignment' => ['horizontal' => 'center']],
        ];
    }

    public function title(): string
    {
        return 'Productos Entrega';
    }

    public function columnWidths(): array
    {
        return [
            //'A' => 8,  // N°
            //'B' => 12, // ID Producto
            'A' => 40, // Producto
            'B' => 15, // Cantidad
        ];
    }
}
