<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    private $repository;

    public function __construct(ServiceTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->all();
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }    

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    /*
    public function index(Request $request)
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
        return $this->repository->destroy($serviceType);
    }

    public function availability(ServiceType $serviceType, Request $request)
    {
       return $this->repository->availability($serviceType, $request);
    }

    public function timeSlots(ServiceType $serviceType, Request $request)
    {
        return $this->repository->timeSlots($serviceType, $request);
    }

    public function pricingRules(ServiceType $serviceType)
    {
        return $this->repository->pricingRules($serviceType);
    }
}