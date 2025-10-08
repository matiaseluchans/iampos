<?php

namespace App\Repositories;

use App\Models\ServiceType;

use Illuminate\Http\Request;


class ServiceTypeRepository extends BaseRepository
{
    public function __construct(ServiceType $m)
    {
        parent::__construct($m);
    }

    /*public function index(Request $request)
    {
        $query = ServiceType::with(['resourceType'])->withCount(['reservations', 'pricingRules']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('requires_resource')) {
            $query->where('requires_resource', $request->requires_resource);
        }

        $serviceTypes = $query->orderBy('name')->paginate(15);

        return response()->json($serviceTypes);
    }

    public function show(ServiceType $serviceType)
    {
        $serviceType->load(['resourceType', 'pricingRules', 'reservations']);
        return response()->json($serviceType);
    }

    public function store(StoreServiceTypeRequest $request)
    {
        $serviceType = ServiceType::create($request->validated());

        return response()->json([
            'message' => 'Tipo de servicio creado exitosamente',
            'service_type' => $serviceType
        ], 201);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType)
    {
        $serviceType->update($request->validated());

        return response()->json([
            'message' => 'Tipo de servicio actualizado exitosamente',
            'service_type' => $serviceType
        ]);
    }
    */
    public function destroy(ServiceType $serviceType)
    {
        if ($serviceType->reservations()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el tipo de servicio porque tiene reservas asociadas'
            ], 422);
        }

        $serviceType->delete();

        return response()->json([
            'message' => 'Tipo de servicio eliminado exitosamente'
        ]);
    }

    public function availability(ServiceType $serviceType, Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'filters' => 'sometimes|array'
        ]);

        if (!$serviceType->requires_resource) {
            return response()->json([
                'available' => true,
                'available_count' => null
            ]);
        }

        $availableCount = $serviceType->availableResourcesCount(
            $request->start_time,
            $request->end_time,
            $request->filters ?? []
        );

        return response()->json([
            'available' => $availableCount > 0,
            'available_count' => $availableCount
        ]);
    }

    public function timeSlots(ServiceType $serviceType, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'filters' => 'sometimes|array'
        ]);

        $timeService = app(\App\Services\TimeCalculationService::class);
        $slots = $timeService->getAvailableTimeSlots(
            $serviceType->id,
            $request->date,
            $request->filters ?? []
        );

        return response()->json($slots);
    }

    public function pricingRules(ServiceType $serviceType)
    {
        $pricingRules = $serviceType->pricingRules()
            ->active()
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json($pricingRules);
    }
    
}
