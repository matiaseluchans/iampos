<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsListAndStockExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents  // Importante: agregar esta interfaz
{
    protected $products;
    protected $allPriceListsNames;

    public function __construct(Collection $products)
    {
        $this->products = $products;
        $this->allPriceListsNames = $this->getAllPriceListsNames();
    }

    /**
     * Obtener todos los nombres únicos de listas de precios
     */
    private function getAllPriceListsNames(): array
    {
        $priceListsNames = [];

        foreach ($this->products as $product) {
            foreach ($product->priceLists as $priceList) {
                $name = $priceList->name ?? ($priceList->name ?? 'Sin nombre');
                if (!in_array($name, $priceListsNames)) {
                    $priceListsNames[] = $name;
                }
            }
        }

        sort($priceListsNames);
        return $priceListsNames;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        $headings = [
            'ID',
            'Código',
            'Nombre',
            'Stock Disponible',
            'Precio Compra',
        ];
        //$headings[] = 'Stock Disponible';

        foreach ($this->allPriceListsNames as $priceListName) {
            $headings[] = "Precio: " . $priceListName;
        }


        return $headings;
    }

    public function map($product): array
    {
        try {
            $stockTotal = 0;
            if ($product->relationLoaded('stocks') && $product->stocks) {
                $stockTotal = $product->stocks->sum('quantity');
            }

            $codigo = $product->code ?? 'N/A';

            // Si es numérico y muy largo, lo convertimos a string
            if (is_numeric($codigo) && strlen((string)$codigo) > 10) {
                $codigo = (string)$codigo . ' ';
            }


            $row = [
                $product->id ?? 0,
                $codigo,
                $product->name ?? '',
                $stockTotal,
                $product->precio_compra ?? ($product->purchase_price ?? 0),
            ];

            //$row[] = $stockTotal;

            foreach ($this->allPriceListsNames as $priceListName) {
                $price = 0;
                if ($product->relationLoaded('priceLists') && $product->priceLists) {
                    foreach ($product->priceLists as $priceList) {
                        $currentName = $priceList->name ?? 'Sin nombre';
                        if ($currentName === $priceListName) {
                            $price = $priceList->pivot->sale_price ??
                                ($priceList->pivot->precio ?? 0);
                            break;
                        }
                    }
                }
                $row[] = number_format($price, 2, '.', '');
            }


            return $row;
        } catch (\Exception $e) {
            \Log::error('Error mapping product: ' . $e->getMessage());
            return array_fill(0, count($this->headings()), 'Error');
        }
    }

    public function title(): string
    {
        return 'Productos';
    }

    /**
     * Configurar estilos y formatos
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Obtener la hoja activa
                $sheet = $event->sheet->getDelegate();

                // Calcular la última fila con datos
                $lastRow = $this->products->count() + 1; // +1 por los encabezados

                // ====================
                // 1. ESTILIZAR ENCABEZADOS (fila 1)
                // ====================
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '4472C4'] // Azul corporativo
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF']
                        ]
                    ]
                ];

                $event->sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

                // ====================
                // 2. ESTILO COLUMNA B (CÓDIGO) - NEGRITA
                // ====================
                $columnBStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ]
                ];

                // Aplicar a toda la columna B (código), excluyendo encabezado
                $event->sheet->getStyle('B2:B' . $lastRow)->applyFromArray($columnBStyle);

                // ====================
                // 3. ESTILO COLUMNA K - COLOR DE FONDO
                // ====================
                // Primero necesitamos saber qué letra es la columna K
                // Las columnas son: A=1, B=2, C=3, D=4, E=5, F=6, G=7, H=8, I=9, J=10, K=11
                // Pero necesitamos calcular dinámicamente porque las listas de precios son variables

                // Calcular la columna de Stock Disponible
                // Columnas fijas: A(ID), B(Código), C(Nombre), D(Precio Compra) = 4 columnas
                // + N columnas de precios de listas
                $fixedColumns = 4; // A, B, C, D
                $priceListsColumns = count($this->allPriceListsNames);
                $stockColumnIndex = $fixedColumns; // Columna del stock

                // Convertir índice numérico a letra de columna (A=1, B=2, etc.)
                $stockColumnLetter = $this->getColumnLetter($stockColumnIndex);

                \Log::info("Columna Stock: índice {$stockColumnIndex}, letra {$stockColumnLetter}");

                // Aplicar estilo a la columna de Stock
                $stockColumnStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'D9D9D9'] //  claro pastel
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'C6E0B4']
                        ]
                    ]
                ];

                // Aplicar estilo a toda la columna de Stock (incluyendo encabezado)
                $event->sheet->getStyle($stockColumnLetter . '2:' . $stockColumnLetter . $lastRow)->applyFromArray($stockColumnStyle);

                // ====================
                // 4. ESTILOS ADICIONALES PARA MEJOR VISUALIZACIÓN
                // ====================

                // a) Bordes para todas las celdas con datos
                $dataRange = 'A1:' . $sheet->getHighestColumn() . $lastRow;
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9D9']
                        ]
                    ]
                ];
                $event->sheet->getStyle($dataRange)->applyFromArray($borderStyle);

                // b) Formato numérico para columnas de precios y stock
                // Precio Compra (columna D)
                $event->sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

                // Columnas de precios de listas
                for ($i = 0; $i < $priceListsColumns; $i++) {
                    $colLetter = $this->getColumnLetter($fixedColumns + $i);
                    $event->sheet->getStyle($colLetter . '2:' . $colLetter . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // c) Alineación para ID (columna A)
                $event->sheet->getStyle('A2:A' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]);
                //$event->sheet->getStyle('B2:B' . $lastRow)->getNumberFormat()->setFormatCode('@');
                $event->sheet->getStyle('B2:B' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                // También establecer el formato para toda la columna B
                $sheet->getStyle('B')
                    ->getNumberFormat()
                    ->setFormatCode('@'); // '@' también representa texto

                // d) Altura de fila para encabezado
                $sheet->getRowDimension(1)->setRowHeight(25);

                // e) Autoajustar ancho de columnas
                foreach (range('A', $sheet->getHighestColumn()) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // f) Congelar paneles (fijar encabezados)
                $sheet->freezePane('A2');

                // ====================
                // 5. ESTILO CONDICIONAL PARA STOCK BAJO (opcional)
                // ====================
                $conditionalStyle = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $conditionalStyle->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
                $conditionalStyle->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
                $conditionalStyle->addCondition(5); // Si stock es menor a 5
                $conditionalStyle->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
                $conditionalStyle->getStyle()->getFill()->getStartColor()->setRGB('A0A0A0A'); // Rojo claro
                //$conditionalStyle->getStyle()->getFont()->getColor()->setRGB('9C0006'); // Rojo oscuro

                // Aplicar estilo condicional a la columna de stock
                $conditionalStyles = $sheet->getStyle($stockColumnLetter . '2:' . $stockColumnLetter . $lastRow)->getConditionalStyles();
                $conditionalStyles[] = $conditionalStyle;
                $sheet->getStyle($stockColumnLetter . '2:' . $stockColumnLetter . $lastRow)->setConditionalStyles($conditionalStyles);

                // ====================
                // 6. AGREGAR FILTROS A LOS ENCABEZADOS
                // ====================
                $sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . '1');
            },
        ];
    }

    /**
     * Convertir número de columna a letra (1 = A, 2 = B, etc.)
     */
    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $temp = ($columnNumber - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $columnNumber = intval(($columnNumber - $temp) / 26);
        }
        return $letter;
    }
}
