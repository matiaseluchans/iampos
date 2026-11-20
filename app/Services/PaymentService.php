<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SupplierPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Query base para el resumen de pagos
     */
    public function getPaymentsSummaryQuery($filters = [])
    {
        $query = Payment::join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select([
                DB::raw('DATE(payments.payment_date) as date'),
                'payments.payment_method_id',
                'payment_methods.name as payment_method_name',
                DB::raw('SUM(payments.amount) as amount'),
                DB::raw('COUNT(payments.id) as payment_count')
            ]);

        // Aplicar filtro de métodos de pago
        if (!empty($filters['payment_methods'])) {
            $query->whereIn('payments.payment_method_id', $filters['payment_methods']);
        }

        return $query->groupBy('date', 'payments.payment_method_id', 'payment_methods.name',)
            ->orderBy('date', 'desc')
            ->orderBy('amount', 'desc');
    }

    /**
     * Resumen unificado por día y método de pago: ingresos (payments de ventas, los reembolsos
     * restan) y egresos (supplier_payments de compras, en negativo). net = income + expense.
     *
     * Filtros: payment_methods (ids|null), start_date, end_date (sin ninguna: últimos 30 días),
     * origin (all|sales|purchases).
     *
     * @return Collection<int, array{date:string, payment_method_id:int, payment_method_name:string, income:float, expense:float, net:float, payment_count:int}>
     */
    public function getUnifiedSummary(array $filters = []): Collection
    {
        $origin = $filters['origin'] ?? 'all';
        $methods = $filters['payment_methods'] ?? null;
        $rows = [];

        if ($origin !== 'purchases') {
            $query = $this->getPaymentsSummaryQuery(['payment_methods' => $methods]);
            $this->applyDateRange($query, 'payments.payment_date', $filters);
            foreach ($query->get() as $r) {
                $this->addSummaryRow($rows, $r, 'income', (float) $r->amount);
            }
        }

        if ($origin !== 'sales') {
            $query = SupplierPayment::join('payment_methods', 'supplier_payments.payment_method_id', '=', 'payment_methods.id')
                ->select([
                    DB::raw('DATE(supplier_payments.payment_date) as date'),
                    'supplier_payments.payment_method_id',
                    'payment_methods.name as payment_method_name',
                    DB::raw('SUM(supplier_payments.amount) as amount'),
                    DB::raw('COUNT(supplier_payments.id) as payment_count'),
                ]);
            if (!empty($methods)) {
                $query->whereIn('supplier_payments.payment_method_id', $methods);
            }
            $this->applyDateRange($query, 'supplier_payments.payment_date', $filters);
            $query->groupBy('date', 'supplier_payments.payment_method_id', 'payment_methods.name');
            foreach ($query->get() as $r) {
                $this->addSummaryRow($rows, $r, 'expense', -(float) $r->amount);
            }
        }

        return collect($rows)
            ->map(function ($row) {
                $row['income'] = round($row['income'], 2);
                $row['expense'] = round($row['expense'], 2);
                $row['net'] = round($row['income'] + $row['expense'], 2);
                return $row;
            })
            ->sort(fn($a, $b) => [$b['date'], abs($b['net'])] <=> [$a['date'], abs($a['net'])])
            ->values();
    }

    /**
     * Detalle de pagos de un día y método (ventas y/o compras), más reciente primero.
     */
    public function getPaymentDetails($date, $paymentMethodId, string $origin = 'all'): Collection
    {
        return $this->getPaymentDetailsRange([
            'start_date' => $date,
            'end_date' => $date,
            'payment_methods' => [$paymentMethodId],
            'origin' => $origin,
        ]);
    }

    /**
     * Detalle de pagos de un rango (mismos filtros que getUnifiedSummary), más reciente primero.
     * El monto de las compras es negativo (egreso).
     *
     * @return Collection<int, array{id:int, origin:string, date:string, payment_date:string, payment_method_id:int, payment_method:string, reference_number:string, counterparty_name:?string, counterparty_contact:string, amount:float, status:?string}>
     */
    public function getPaymentDetailsRange(array $filters = []): Collection
    {
        $origin = $filters['origin'] ?? 'all';
        $methods = $filters['payment_methods'] ?? null;
        $rows = collect();

        if ($origin !== 'purchases') {
            $query = Payment::join('orders', 'payments.order_id', '=', 'orders.id')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
                ->leftJoin('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                ->select([
                    'payments.id',
                    'payments.payment_date',
                    'payments.payment_method_id',
                    'payments.amount',
                    'payment_methods.name as payment_method_name',
                    'orders.order_number',
                    DB::raw("CONCAT(customers.address, ', ', customers.firstname) as customer_name"),
                    DB::raw("COALESCE(customers.telephone, 'Sin contacto') as customer_contact"),
                    'payment_statuses.name as status_name',
                ]);
            if (!empty($methods)) {
                $query->whereIn('payments.payment_method_id', $methods);
            }
            $this->applyDateRange($query, 'payments.payment_date', $filters);

            foreach ($query->get() as $p) {
                $rows->push($this->detailRow('sale', $p, $p->order_number, $p->customer_name, $p->customer_contact, (float) $p->amount, $p->status_name));
            }
        }

        if ($origin !== 'sales') {
            $query = SupplierPayment::join('purchases', 'supplier_payments.purchase_id', '=', 'purchases.id')
                ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->join('payment_methods', 'supplier_payments.payment_method_id', '=', 'payment_methods.id')
                ->select([
                    'supplier_payments.id',
                    'supplier_payments.payment_date',
                    'supplier_payments.payment_method_id',
                    'supplier_payments.amount',
                    'payment_methods.name as payment_method_name',
                    'purchases.purchase_number',
                    'purchases.payment_status as purchase_payment_status',
                    'suppliers.name as supplier_name',
                    DB::raw("COALESCE(suppliers.telephone, 'Sin contacto') as supplier_contact"),
                ]);
            if (!empty($methods)) {
                $query->whereIn('supplier_payments.payment_method_id', $methods);
            }
            $this->applyDateRange($query, 'supplier_payments.payment_date', $filters);

            foreach ($query->get() as $p) {
                $rows->push($this->detailRow('purchase', $p, $p->purchase_number, $p->supplier_name, $p->supplier_contact, -(float) $p->amount, $p->purchase_payment_status));
            }
        }

        return $rows->sortByDesc('payment_date')->values();
    }

    /** Filtro de fechas inclusivo (día completo); sin ninguna fecha, últimos 30 días. */
    private function applyDateRange($query, string $column, array $filters): void
    {
        $start = $filters['start_date'] ?? null;
        $end = $filters['end_date'] ?? null;

        if ($start) {
            $query->where($column, '>=', Carbon::parse($start)->startOfDay());
        }
        if ($end) {
            $query->where($column, '<=', Carbon::parse($end)->endOfDay());
        }
        if (!$start && !$end) {
            $query->where($column, '>=', now()->subDays(30));
        }
    }

    private function addSummaryRow(array &$rows, $r, string $column, float $amount): void
    {
        $key = $r->date . '|' . $r->payment_method_id;
        $rows[$key] ??= [
            'date' => $r->date,
            'payment_method_id' => (int) $r->payment_method_id,
            'payment_method_name' => $r->payment_method_name,
            'income' => 0.0,
            'expense' => 0.0,
            'net' => 0.0,
            'payment_count' => 0,
        ];
        $rows[$key][$column] += $amount;
        $rows[$key]['payment_count'] += (int) $r->payment_count;
    }

    private function detailRow(string $origin, $p, $reference, $counterparty, $contact, float $amount, $status): array
    {
        $paymentDate = $p->getRawOriginal('payment_date');

        return [
            'id' => $p->id,
            'origin' => $origin,
            'date' => Carbon::parse($paymentDate)->format('Y-m-d'),
            'payment_date' => $paymentDate,
            'payment_method_id' => (int) $p->payment_method_id,
            'payment_method' => $p->payment_method_name,
            'reference_number' => $reference,
            'counterparty_name' => $counterparty,
            'counterparty_contact' => $contact,
            'amount' => $amount,
            'status' => $status,
        ];
    }

    /**
     * Obtener métodos de pago disponibles
     */
    public function getPaymentMethods()
    {
        return \App\Models\PaymentMethod::active()
            ->select('id', 'name')
            ->get();
    }
}
