<?php

namespace App\Exports;

use App\Services\PaymentService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PaymentsSummaryExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $paymentMethods;
    protected $paymentService;

    public function __construct($startDate, $endDate, $paymentMethods = null, PaymentService $paymentService)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethods = $paymentMethods;
        $this->paymentService = $paymentService;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new PaymentsSummarySheet(
            $this->startDate,
            $this->endDate,
            $this->paymentMethods,
            $this->paymentService
        );

        $sheets[] = new PaymentsDetailSheet(
            $this->startDate,
            $this->endDate,
            $this->paymentMethods,
            $this->paymentService
        );

        return $sheets;
    }
}

class PaymentsSummarySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $paymentMethods;
    protected $paymentService;

    public function __construct($startDate, $endDate, $paymentMethods = null, PaymentService $paymentService)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethods = $paymentMethods;
        $this->paymentService = $paymentService;
    }

    public function collection()
    {
        $filters = [
            'payment_methods' => $this->paymentMethods,
        ];

        // Usar el mismo query del servicio
        $query = $this->paymentService->getPaymentsSummaryQuery($filters);

        // Aplicar los mismos filtros de fecha que en getSummary()
        if ($this->startDate && $this->endDate) {
            $query->where('payments.payment_date', '>=', \Carbon\Carbon::parse($this->startDate)->startOfDay())
                ->where('payments.payment_date', '<=', \Carbon\Carbon::parse($this->endDate)->endOfDay());
        } else if ($this->startDate) {
            $query->where('payments.payment_date', '>=', \Carbon\Carbon::parse($this->startDate)->startOfDay());
        } else if ($this->endDate) {
            $query->where('payments.payment_date', '<=', \Carbon\Carbon::parse($this->endDate)->endOfDay());
        } else {
            // Por defecto últimos 30 días (igual que en el controller)
            $query->where('payments.payment_date', '>=', now()->subDays(30));
        }

        // Obtener todos los resultados sin paginación
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Método de Pago',
            'Tipo de Método',
            'Monto Total',
            'Cantidad de Transacciones',
            'Tipo de Monto'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->date,
            $payment->payment_method_name,
            $payment->payment_method_code,
            abs($payment->amount),
            $payment->transaction_count,
            $payment->amount >= 0 ? 'Ingreso' : 'Reembolso'
        ];
    }

    public function title(): string
    {
        return 'Resumen Pagos';
    }
}

class PaymentsDetailSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $paymentMethods;
    protected $paymentService;

    public function __construct($startDate, $endDate, $paymentMethods = null, PaymentService $paymentService)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethods = $paymentMethods;
        $this->paymentService = $paymentService;
    }

    public function collection()
    {
        // Primero obtenemos los grupos de resumen
        $filters = [
            'payment_methods' => $this->paymentMethods,
        ];

        $summaryQuery = $this->paymentService->getPaymentsSummaryQuery($filters);

        // Aplicar filtros de fecha (igual que en el resumen)
        if ($this->startDate && $this->endDate) {
            $summaryQuery->where('payments.payment_date', '>=', \Carbon\Carbon::parse($this->startDate)->startOfDay())
                ->where('payments.payment_date', '<=', \Carbon\Carbon::parse($this->endDate)->endOfDay());
        } else if ($this->startDate) {
            $summaryQuery->where('payments.payment_date', '>=', \Carbon\Carbon::parse($this->startDate)->startOfDay());
        } else if ($this->endDate) {
            $summaryQuery->where('payments.payment_date', '<=', \Carbon\Carbon::parse($this->endDate)->endOfDay());
        } else {
            $summaryQuery->where('payments.payment_date', '>=', now()->subDays(30));
        }

        $summaryGroups = $summaryQuery->get();

        // Colección para todos los detalles
        $allDetails = collect();

        // Para cada grupo, obtener los detalles
        foreach ($summaryGroups as $group) {
            try {
                $details = $this->paymentService->getPaymentDetails($group->date, $group->payment_method_id);

                // Convertir a colección si es array y agregar información del grupo
                $detailsCollection = collect($details)->map(function ($detail) use ($group) {
                    // Si es array, convertirlo a objeto
                    if (is_array($detail)) {
                        $detail = (object) $detail;
                    }

                    // Agregar información del grupo
                    $detail->group_date = $group->date;
                    $detail->payment_method_name = $group->payment_method_name;
                    $detail->payment_method_code = $group->payment_method_code;

                    return $detail;
                });

                $allDetails = $allDetails->merge($detailsCollection);
            } catch (\Exception $e) {
                // Continuar con los siguientes grupos si hay error en uno
                continue;
            }
        }

        return $allDetails;
    }

    public function headings(): array
    {
        return [
            'Fecha Pago',
            'Fecha Grupo',
            'Método de Pago',
            'ID Orden',
            'Cliente',
            'Contacto Cliente',
            'Monto',
            'Tipo Monto',
            'Estado Orden'
        ];
    }

    public function map($payment): array
    {
        // Manejar tanto arrays como objetos
        $paymentDate = is_array($payment) ?
            ($payment['payment_date'] ?? $payment['created_at'] ?? '') : ($payment->payment_date ?? $payment->created_at ?? '');

        $groupDate = is_array($payment) ?
            ($payment['group_date'] ?? $payment['date'] ?? '') : ($payment->group_date ?? $payment->date ?? '');

        $paymentMethodName = is_array($payment) ?
            ($payment['payment_method_name'] ?? 'N/A') : ($payment->payment_method_name ?? 'N/A');

        $orderNumber = is_array($payment) ?
            ($payment['order_number'] ?? 'N/A') : ($payment->order_number ?? 'N/A');

        $customerName = is_array($payment) ?
            ($payment['customer_name'] ?? 'N/A') : ($payment->customer_name ?? 'N/A');

        $customerContact = is_array($payment) ?
            ($payment['customer_contact'] ?? 'N/A') : ($payment->customer_contact ?? 'N/A');

        $amount = is_array($payment) ?
            ($payment['amount'] ?? 0) : ($payment->amount ?? 0);

        $paymentStatus = is_array($payment) ?
            ($payment['order']['paymentStatus']['name'] ?? 'N/A') : ($payment->order->paymentStatus->name ?? 'N/A');

        return [
            $paymentDate,
            $groupDate,
            $paymentMethodName,
            $orderNumber,
            $customerName,
            $customerContact,
            $amount,
            $amount >= 0 ? 'Ingreso' : 'Reembolso',
            $paymentStatus
        ];
    }

    public function title(): string
    {
        return 'Detalle Pagos';
    }
}
