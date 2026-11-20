<?php

namespace App\Exports;

use App\Services\PaymentService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PaymentsSummaryExport implements WithMultipleSheets
{
    protected array $filters;
    protected PaymentService $paymentService;

    public function __construct($startDate, $endDate, ?array $paymentMethods, PaymentService $paymentService, string $origin = 'all')
    {
        $this->filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_methods' => $paymentMethods,
            'origin' => $origin,
        ];
        $this->paymentService = $paymentService;
    }

    public function sheets(): array
    {
        return [
            new PaymentsSummarySheet($this->filters, $this->paymentService),
            new PaymentsDetailSheet($this->filters, $this->paymentService),
        ];
    }
}

class PaymentsSummarySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected array $filters, protected PaymentService $paymentService)
    {
    }

    public function collection(): Collection
    {
        // Mismo resumen (y mismos filtros, incluido el default de 30 días) que la pantalla
        return $this->paymentService->getUnifiedSummary($this->filters);
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Método de Pago',
            'Ingresos',
            'Egresos',
            'Neto',
            'Cantidad de Transacciones',
        ];
    }

    public function map($row): array
    {
        return [
            $row['date'],
            $row['payment_method_name'],
            $row['income'],
            $row['expense'],
            $row['net'],
            $row['payment_count'],
        ];
    }

    public function title(): string
    {
        return 'Resumen Pagos';
    }
}

class PaymentsDetailSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected array $filters, protected PaymentService $paymentService)
    {
    }

    public function collection(): Collection
    {
        // Una sola consulta del rango, sin recorrer grupo por grupo
        return $this->paymentService->getPaymentDetailsRange($this->filters);
    }

    public function headings(): array
    {
        return [
            'Fecha Pago',
            'Fecha Grupo',
            'Método de Pago',
            'Origen',
            'Referencia',
            'Contraparte',
            'Contacto',
            'Monto',
            'Tipo Monto',
            'Estado',
        ];
    }

    public function map($row): array
    {
        $isPurchase = $row['origin'] === 'purchase';

        if ($isPurchase) {
            $type = 'Egreso';
        } else {
            $type = $row['amount'] >= 0 ? 'Ingreso' : 'Reembolso';
        }

        return [
            $row['payment_date'],
            $row['date'],
            $row['payment_method'],
            $isPurchase ? 'Compra' : 'Venta',
            $row['reference_number'],
            $row['counterparty_name'],
            $row['counterparty_contact'],
            $row['amount'],
            $type,
            $row['status'] ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Detalle Pagos';
    }
}
