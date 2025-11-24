<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Carbon\Carbon;

use App\Exports\PaymentsSummaryExport;
use Maatwebsite\Excel\Facades\Excel;

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

    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $paymentMethods = $request->input('payment_methods');

            \Log::info('🔍 EXPORT EXCEL - Iniciando exportación', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_methods' => $paymentMethods
            ]);

            // Formatear las fechas para el nombre del archivo
            $formattedStartDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : 'todo';
            $formattedEndDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : 'hoy';

            $fileName = "resumen_pagos_{$formattedStartDate}_{$formattedEndDate}.xlsx";

            \Log::info('📄 EXPORT EXCEL - Nombre del archivo: ' . $fileName);

            // Verificar que el servicio esté disponible
            if (!$this->paymentService) {
                \Log::error('❌ PaymentService no está disponible');
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor: PaymentService no disponible'
                ], 500);
            }

            \Log::info('🔄 EXPORT EXCEL - Creando instancia de PaymentsSummaryExport');

            $export = new PaymentsSummaryExport($startDate, $endDate, $paymentMethods, $this->paymentService);

            \Log::info('✅ EXPORT EXCEL - Export creado exitosamente, procediendo a descarga');

            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            \Log::error('💥 ERROR en exportExcel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el archivo Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
