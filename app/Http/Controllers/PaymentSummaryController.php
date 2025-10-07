<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Carbon\Carbon;

class PaymentSummaryController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Obtener resumen de pagos agrupados por día y método de pago
     */
    public function getSummary(Request $request)
    {

        $filters = [
            'payment_methods' => $request->input('payment_methods'),
        ];

        $query = $this->paymentService->getPaymentsSummaryQuery($filters);

        // Aplicar filtros de fecha
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');



        if ($startDate && $endDate) {
            // Usar where normal con Carbon para manejar el formato ISO
            $query->where('payments.payment_date', '>=', Carbon::parse($startDate)->startOfDay())
                ->where('payments.payment_date', '<=', Carbon::parse($endDate)->endOfDay());
        } else if ($startDate) {
            // Solo fecha inicial
            $query->where('payments.payment_date', '>=', Carbon::parse($startDate)->startOfDay());
        } else if ($endDate) {
            // Solo fecha final
            $query->where('payments.payment_date', '<=', Carbon::parse($endDate)->endOfDay());
        } else {
            // Por defecto últimos 30 días
            $query->where('payments.payment_date', '>=', now()->subDays(30));
        }

        //var_dump($query->toSql(), $query->getBindings());

        // Paginación
        $perPage = $request->input('per_page', 10);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'total' => $payments->total(),
            'current_page' => $payments->currentPage(),
            'last_page' => $payments->lastPage(),
            'per_page' => $payments->perPage()
        ]);
    }

    /**
     * Obtener detalle de pagos para un día y método de pago específico
     */
    public function getDetail(Request $request)
    {
        try {
            $date = $request->input('date');
            $paymentMethodId = $request->input('payment_method_id');

            if (!$date || !$paymentMethodId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fecha y método de pago son requeridos'
                ], 400);
            }

            $details = $this->paymentService->getPaymentDetails($date, $paymentMethodId);

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle de pagos'
            ], 500);
        }
    }
}
