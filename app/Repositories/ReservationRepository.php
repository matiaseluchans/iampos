<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Requests\CancelReservationRequest;
use Illuminate\Http\Request;
use App\Services\TimeCalculationService;
use App\Services\CapacityService;
use App\Services\PricingService;
use App\Models\ServiceType;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationRepository extends  BaseRepository
{
    protected $timeService;
    protected $capacityService;
    protected $pricingService;

    public function __construct(
        Reservation $m,
        TimeCalculationService $timeService,
        CapacityService $capacityService,
        PricingService $pricingService
    ) {
        parent::__construct($m);
        $this->timeService = $timeService;
        $this->capacityService = $capacityService;
        $this->pricingService = $pricingService;
    }

    
    
    public function index(Request $request)
    {
        $query = Reservation::with(['customer', 'serviceType', 'resource']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        if ($request->has('resource_id')) {
            $query->where('resource_id', $request->resource_id);
        }

        if ($request->has('upcoming')) {
            $query->where('start_time', '>', now());
        }

        if ($request->has('past')) {
            $query->where('end_time', '<', now());
        }

        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        $reservations = $query->orderBy('start_time', 'desc')->paginate(20);

        return response()->json($reservations);
    }
    /*
    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'serviceType', 'resource', 'quotations', 'payments']);
        return response()->json($reservation);
    }*/

    /**
     * Store a newly created reservation
     */
    public function store(StoreReservationRequest $request)
    {
        DB::beginTransaction();

        try {           
            // Obtener el tipo de servicio
            $serviceType = ServiceType::findOrFail($request->service_type_id);
            
            // Calcular end_time automáticamente
            $endTime = $this->timeService->calculateEndTime(
                $serviceType,
                $request->start_time,
                $request->time_units
            );

            // Verificar y asignar recurso si es necesario
            $resourceId = $this->handleResourceAssignment($request, $serviceType, $endTime);

            // Crear la reserva
            $reservation = $this->createReservation($request, $serviceType, $endTime, $resourceId);

            // Generar cotización automática
            $quotation = $this->generateQuotation($reservation);

            // Confirmar transacción
            DB::commit();

            Log::info('Reserva creada exitosamente', [
                'reservation_id' => $reservation->id,
                'customer_id' => $reservation->customer_id,
                'service_type' => $serviceType->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'data' => [
                    'reservation' => $reservation->load(['customer', 'serviceType', 'resource']),
                    'quotation' => $quotation
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creando reserva', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reserva',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateReservation(UpdateReservationRequest $request, Reservation $reservation)
    {
        try {
            $validated = $request->validated();
            
            // Actualizar la reserva
            $reservation->update($validated);
            
            // Si se cambió el estado a confirmado, actualizar capacidad del recurso
            if ($request->has('status') && $request->status === Reservation::STATUS_CONFIRMED) {
                if ($reservation->resource && $reservation->resource->is_shared) {
                    $reservation->resource->updateCurrentUsage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente',
                'data' => [
                    'reservation' => $reservation->load(['customer', 'serviceType', 'resource'])
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified reservation.
     */
    public function cancel(CancelReservationRequest $request, Reservation $reservation)
    {
        try {
            $validated = $request->validated();
            
            // Cancelar la reserva
            $reservation->cancel($validated['cancellation_reason']);
            
            // Enviar notificación si se solicitó
            if ($request->boolean('send_notification')) {
                // Aquí iría la lógica para enviar notificación al cliente
                // Notification::send($reservation->customer, new ReservationCancelled($reservation));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada exitosamente',
                'data' => [
                    'reservation' => $reservation->fresh(['customer', 'serviceType', 'resource'])
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update only reservation time (for calendar drag & drop)
     */
    public function updateTime(Request $request, Reservation $reservation)
    {
        $request->validate([
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $reservation->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Horario de reserva actualizado exitosamente',
                'data' => [
                    'reservation' => $reservation
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
/*
    public function update($request, $reservation)
    {
        $validated = $request->validated();

        // Si cambian fechas o recurso, verificar disponibilidad
        if (isset($validated['start_time']) || isset($validated['end_time']) || isset($validated['resource_id'])) {
            $startTime = $validated['start_time'] ?? $reservation->start_time;
            $endTime = $validated['end_time'] ?? $reservation->end_time;
            $resourceId = $validated['resource_id'] ?? $reservation->resource_id;

            if ($resourceId) {
                $resource = \App\Models\Resource::find($resourceId);
                
                $available = $this->capacityService->checkCapacityAvailability(
                    $resource->id,
                    $startTime,
                    $endTime,
                    $validated['required_capacity'] ?? $reservation->required_capacity
                );

                if (!$available['available']) {
                    return response()->json([
                        'message' => 'No hay disponibilidad para el recurso seleccionado',
                        'available_capacity' => $available['available_capacity']
                    ], 422);
                }
            }
        }

        $reservation->update($validated);

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
            'reservation' => $reservation->load(['customer', 'serviceType', 'resource'])
        ]);
    }
    */

    public function destroy(Reservation $reservation)
    {
        $reservation->cancel('Cancelada por el administrador');
        
        return response()->json([
            'message' => 'Reserva cancelada exitosamente'
        ]);
    }
/*
    public function cancel($request, $reservation)
    {
        $reservation->cancel($request->reason);
        
        return response()->json([
            'message' => 'Reserva cancelada exitosamente'
        ]);
    }
*/
    public function confirm(Reservation $reservation)
    {
        $reservation->confirm();
        
        return response()->json([
            'message' => 'Reserva confirmada exitosamente',
            'reservation' => $reservation
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'required_capacity' => 'sometimes|integer|min:1'
        ]);

        $serviceType = \App\Models\ServiceType::find($request->service_type_id);
        
        if (!$serviceType->requires_resource) {
            return response()->json(['available' => true]);
        }

        $availability = $this->capacityService->getAvailableCapacityByResourceType(
            $serviceType->resource_type_id,
            $request->start_time,
            $request->end_time
        );

        $requiredCapacity = $request->required_capacity ?? 1;
        $availableResources = array_filter($availability, function($item) use ($requiredCapacity) {
            return $item['available_capacity'] >= $requiredCapacity;
        });

        return response()->json([
            'available' => count($availableResources) > 0,
            'available_resources' => array_values($availableResources)
        ]);
    }

    public function calculateEndTime(Request $request)
    {
        $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'start_time' => 'required|date',
            'time_units' => 'required|integer|min:1'
        ]);

        $serviceType = \App\Models\ServiceType::find($request->service_type_id);
        
        $endTime = $this->timeService->calculateEndTime(
            $serviceType,
            $request->start_time,
            $request->time_units
        );

        return response()->json(['end_time' => $endTime->toDateTimeString()]);
    }

    public function quotations($reservation)
    {
        $quotations = $reservation->quotations()->with(['items'])->get();
        return response()->json($quotations);
    }

    public function payments($reservation)
    {
        $payments = $reservation->payments()->get();
        return response()->json($payments);
    }

    /**
     * Manejar la asignación de recursos
     */
    private function handleResourceAssignment($request, ServiceType $serviceType, $endTime): ?int
    {
        // Si el servicio no requiere recurso, retornar null
        if (!$serviceType->requires_resource) {
            return null;
        }

        // Si se proporcionó un resource_id específico, validar disponibilidad
        if ($request->filled('resource_id')) {
            return $this->validateSpecificResource(
                $request->resource_id,
                $request->start_time,
                $endTime,
                $request->required_capacity
            );
        }

        // Si no se proporcionó resource_id, buscar automáticamente uno disponible
        return $this->findAvailableResource(
            $serviceType->resource_type_id,
            $request->start_time,
            $endTime,
            $request->required_capacity,
            $request->get('filters', [])
        );
    }

    /**
     * Validar un recurso específico
     */
    private function validateSpecificResource($resourceId, $startTime, $endTime, $requiredCapacity): int
    {
        $resource = Resource::findOrFail($resourceId);

        if (!$resource->is_active) {
            throw new \Exception('El recurso seleccionado no está activo');
        }

        $availability = $this->capacityService->checkCapacityAvailability(
            $resource->id,
            $startTime,
            $endTime,
            $requiredCapacity
        );

        if (!$availability['available']) {
            throw new \Exception(
                "No hay disponibilidad en el recurso seleccionado. " .
                "Capacidad disponible: {$availability['available_capacity']}"
            );
        }

        return $resource->id;
    }

    /**
     * Buscar automáticamente un recurso disponible
     */
    private function findAvailableResource($resourceTypeId, $startTime, $endTime, $requiredCapacity, $filters = []): ?int
    {
        $availableResources = $this->capacityService->getAvailableCapacityByResourceType(
            $resourceTypeId,
            $startTime,
            $endTime
        );

        // Filtrar recursos que cumplan con la capacidad requerida
        $suitableResources = collect($availableResources)
            ->filter(function ($item) use ($requiredCapacity) {
                return $item['available_capacity'] >= $requiredCapacity;
            })
            ->sortByDesc('available_capacity') // Preferir recursos con más capacidad disponible
            ->values();

        if ($suitableResources->isEmpty()) {
            throw new \Exception(
                "No hay recursos disponibles para el horario seleccionado. " .
                "Capacidad requerida: {$requiredCapacity}"
            );
        }

        // Aplicar filtros adicionales si se proporcionaron
        if (!empty($filters)) {
            $filteredResources = $this->applyResourceFilters($suitableResources, $filters);
            
            if ($filteredResources->isNotEmpty()) {
                return $filteredResources->first()['resource']['id'];
            }
        }

        // Retornar el primer recurso disponible
        return $suitableResources->first()['resource']['id'];
    }

    /**
     * Aplicar filtros a los recursos disponibles
     */
    private function applyResourceFilters($resources, $filters)
    {
        return $resources->filter(function ($item) use ($filters) {
            $resourceFeatures = $item['resource']['features'] ?? [];
            
            foreach ($filters as $key => $value) {
                if (!isset($resourceFeatures[$key]) || $resourceFeatures[$key] != $value) {
                    return false;
                }
            }
            
            return true;
        });
    }

    /**
     * Crear la reserva en la base de datos
     */
    private function createReservation($request, ServiceType $serviceType, $endTime, $resourceId): Reservation
    {
        // Calcular precio total
        $totalPrice = $this->calculateTotalPrice($request, $serviceType, $endTime);

        $reservationData = [
            'customer_id' => $request->customer_id,
            'service_type_id' => $serviceType->id,
            'resource_id' => $resourceId,
            'start_time' => $request->start_time,
            'end_time' => $endTime->toDateTimeString(),
            'time_units' => $request->time_units,
            'required_capacity' => $request->required_capacity,
            'participants_count' => $request->participants_count,
            'special_requirements' => $request->special_requirements,
            'features' => $request->features,
            'notes' => $request->notes,
            'total_price' => $totalPrice,
            'status' => ($request->status)?$request->status:Reservation::STATUS_PENDING
        ];

        $reservation = Reservation::create($reservationData);

        // Actualizar uso del recurso si es compartido
        if ($resourceId) {
            $this->updateResourceUsage($resourceId);
        }

        return $reservation;
    }

    /**
     * Calcular el precio total de la reserva
     */
    private function calculateTotalPrice($request, ServiceType $serviceType, $endTime): float
    {
        // Crear una instancia temporal de reserva para el cálculo de precios
        $temporaryReservation = new Reservation([
            'service_type_id' => $serviceType->id,
            'start_time' => $request->start_time,
            'end_time' => $endTime,
            'time_units' => $request->time_units,
            'required_capacity' => $request->required_capacity,
            'participants_count' => $request->participants_count
        ]);

        $temporaryReservation->setRelation('serviceType', $serviceType);

        return $this->pricingService->calculatePrice($temporaryReservation);
    }

    /**
     * Generar cotización automática
     */
    private function generateQuotation(Reservation $reservation)
    {
        try {
            return $reservation->generateQuotation();
        } catch (\Exception $e) {
            Log::warning('Error generando cotización automática', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            
            // No lanzar excepción para no revertir la reserva
            return null;
        }
    }

    /**
     * Actualizar el uso del recurso
     */
    private function updateResourceUsage($resourceId): void
    {
        try {
            $resource = Resource::find($resourceId);
            if ($resource && $resource->is_shared) {
                $resource->updateCurrentUsage();
            }
        } catch (\Exception $e) {
            Log::error('Error actualizando uso del recurso', [
                'resource_id' => $resourceId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
