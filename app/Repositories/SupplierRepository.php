<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class SupplierRepository extends BaseRepository
{
    private const PROTECTED_FIELDS = ['id', 'tenant_id', 'created_by', 'last_modified_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(Supplier $m)
    {
        parent::__construct($m, ['locality']);
    }

    public function all()
    {
        try {
            return $this->successResponse(
                $this->model->query()->with('locality')->orderBy('name')->get()
            );
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($request)
    {
        try {
            $form = $request->all();

            if ($message = $this->validationError($form, null, true)) {
                return $this->domainError($message);
            }

            $supplier = Supplier::create(Arr::except($form, self::PROTECTED_FIELDS));

            return $this->successResponseCreate($supplier->load('locality'));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function update($request, $id)
    {
        try {
            $form = $request->input('data', []);
            $supplier = $this->ownSupplier($id);

            if ($message = $this->validationError($form, $supplier->id, false)) {
                return $this->domainError($message);
            }

            $supplier->update(Arr::except($form, self::PROTECTED_FIELDS));

            return $this->successResponse($supplier->load('locality'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->domainError('El proveedor no existe', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function delete($id)
    {
        try {
            $supplier = $this->ownSupplier($id);

            if ($supplier->purchases()->exists()) {
                return $this->domainError('No se puede eliminar un proveedor con compras registradas. Inactivalo en su lugar.');
            }

            $supplier->delete();

            return $this->successResponse($supplier);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->domainError('El proveedor no existe', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Cuenta corriente: compras recibidas, pagado y saldo del proveedor.
     */
    public function statement($id)
    {
        try {
            $supplier = $this->ownSupplier($id);

            $purchases = $supplier->purchases()
                ->where('status', 'received')
                ->orderBy('purchase_date', 'desc')
                ->get(['id', 'purchase_number', 'purchase_date', 'due_date', 'supplier_document', 'total_amount', 'total_paid', 'payment_status']);

            $today = now()->startOfDay();
            $rows = $purchases->map(function ($p) use ($today) {
                $row = $p->toArray();
                $row['overdue'] = $p->payment_status !== 'paid'
                    && $p->due_date !== null
                    && $p->due_date->startOfDay()->lt($today);

                return $row;
            })->values();

            $totalPurchased = round($purchases->sum('total_amount'), 2);
            $totalPaid = round($purchases->sum('total_paid'), 2);

            return $this->successResponse([
                'supplier' => $supplier,
                'total_purchased' => $totalPurchased,
                'total_paid' => $totalPaid,
                'balance' => round($totalPurchased - $totalPaid, 2),
                'purchases' => $rows,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->domainError('El proveedor no existe', 404);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Solo proveedores del tenant del usuario: AuditModelTrait reescribe tenant_id al guardar,
     * y TenantScope no filtra para el superadmin, así que no se debe operar sobre registros ajenos.
     */
    private function ownSupplier($id): Supplier
    {
        return Supplier::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
    }

    /**
     * Devuelve el mensaje de error o null si es válido.
     */
    private function validationError(array $form, ?int $ignoreId, bool $creating): ?string
    {
        if ($creating || array_key_exists('name', $form)) {
            if (trim((string) ($form['name'] ?? '')) === '') {
                return 'El campo nombre es requerido';
            }
        }

        $taxId = trim((string) ($form['tax_id'] ?? ''));
        if ($taxId !== '') {
            $exists = Supplier::where('tax_id', $taxId)
                ->where('tenant_id', Auth::user()->tenant_id)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if ($exists) {
                return 'Ya existe un proveedor con ese CUIT';
            }
        }

        if (isset($form['payment_term_days']) && (!is_numeric($form['payment_term_days']) || $form['payment_term_days'] < 0)) {
            return 'El plazo de pago debe ser un número mayor o igual a 0';
        }

        return null;
    }
}
