<?php

namespace App\Repositories;

use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Support\DateInput;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierPaymentRepository extends BaseRepository
{
    public function __construct(SupplierPayment $m)
    {
        parent::__construct($m, ['paymentMethod']);
    }

    /**
     * Body: { purchase_id, payments: [{ payment_method_id: {id}, amount, payment_date (d/m/Y), reference?, notes? }] }
     */
    public function save($request)
    {
        try {
            $form = $request->all();
            $user = Auth::user();

            $purchase = DB::transaction(function () use ($form, $user) {
                $purchase = Purchase::where('id', $form['purchase_id'] ?? 0)
                    ->where('tenant_id', $user->tenant_id)
                    ->lockForUpdate()
                    ->first();
                if (!$purchase) {
                    throw new \DomainException('La compra no existe');
                }
                if ($purchase->status !== Purchase::STATUS_RECEIVED) {
                    throw new \DomainException('No se pueden registrar pagos en una compra anulada');
                }

                $payments = $form['payments'] ?? [];
                if (!is_array($payments) || count($payments) === 0) {
                    throw new \DomainException('Indicá al menos un pago');
                }

                $rows = [];
                foreach ($payments as $payment) {
                    $amount = $payment['amount'] ?? null;
                    if (!is_numeric($amount) || (float) $amount <= 0) {
                        throw new \DomainException('El monto de cada pago debe ser mayor a 0');
                    }

                    $methodId = $payment['payment_method_id']['id'] ?? $payment['payment_method_id'] ?? null;
                    if (!$methodId || !PaymentMethod::where('id', $methodId)->where('tenant_id', $user->tenant_id)->exists()) {
                        throw new \DomainException('El método de pago no es válido');
                    }

                    $rows[] = [
                        'payment_method_id' => $methodId,
                        'amount' => round((float) $amount, 2),
                        'payment_date' => (DateInput::parse($payment['payment_date'] ?? null, 'fecha de pago') ?? Carbon::now())->format('Y-m-d H:i:s'),
                        'reference' => $payment['reference'] ?? null,
                        'notes' => $payment['notes'] ?? null,
                    ];
                }

                $balance = round((float) $purchase->total_amount - (float) $purchase->payments()->lockForUpdate()->sum('amount'), 2);
                $newTotal = round(array_sum(array_column($rows, 'amount')), 2);
                if ($newTotal > $balance) {
                    throw new \DomainException('El pago supera el saldo pendiente de la compra ($' . number_format($balance, 2, ',', '.') . ')');
                }

                foreach ($rows as $row) {
                    SupplierPayment::create($row + ['purchase_id' => $purchase->id]);
                }

                $purchase->refreshPaymentStatus();

                return $purchase;
            });

            return $this->successResponseCreate([
                'purchase' => $purchase->fresh(),
                'payments' => $purchase->payments()->with('paymentMethod')->get(),
            ]);
        } catch (\DomainException $e) {
            return $this->domainError($e->getMessage());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Anula un pago (soft delete) y recalcula el estado de pago de la compra.
     */
    public function delete($id)
    {
        try {
            $purchase = DB::transaction(function () use ($id) {
                $payment = SupplierPayment::where('tenant_id', Auth::user()->tenant_id)->find($id);
                if (!$payment) {
                    throw new \DomainException('El pago no existe');
                }

                $purchase = Purchase::where('id', $payment->purchase_id)->where('tenant_id', Auth::user()->tenant_id)->lockForUpdate()->first();
                $payment->delete();
                $purchase->refreshPaymentStatus();

                return $purchase;
            });

            return $this->successResponse($purchase->fresh());
        } catch (\DomainException $e) {
            return $this->domainError($e->getMessage(), 404);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
