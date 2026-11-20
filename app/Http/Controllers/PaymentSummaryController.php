<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Carbon\Carbon;

use App\Exports\PaymentsSummaryExport;
use Maatwebsite\Excel\Facades\Excel;

class PaymentSummaryController extends Controller
{
    private const ORIGINS = ['all', 'sales', 'purchases'];

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Resumen de pagos por día y método: ingresos (ventas), egresos (compras) y neto.
     * Parámetros: start_date, end_date, payment_methods[], origin (all|sales|purchases).
     */
    public function getSummary(Request $request)
    {
        $origin = $request->input('origin', 'all');
        if (!in_array($origin, self::ORIGINS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'El origen debe ser all, sales o purchases'
            ], 422);
        }

        $rows = $this->paymentService->getUnifiedSummary([
            'payment_methods' => $request->input('payment_methods'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'origin' => $origin,
        ]);

        return response()->json([
            'success' => true,
            'data' => $rows,
            'total' => $rows->count(), // Total de registros
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
            $origin = $request->input('origin', 'all');

            if (!$date || !$paymentMethodId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fecha y método de pago son requeridos'
                ], 400);
            }
            if (!in_array($origin, self::ORIGINS, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El origen debe ser all, sales o purchases'
                ], 422);
            }

            $details = $this->paymentService->getPaymentDetails($date, $paymentMethodId, $origin);

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

            $origin = $request->input('origin', 'all');
            if (!in_array($origin, self::ORIGINS, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El origen debe ser all, sales o purchases'
                ], 422);
            }

            $export = new PaymentsSummaryExport($startDate, $endDate, $paymentMethods, $this->paymentService, $origin);

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
