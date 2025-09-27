<?php

namespace App\Repositories;

use App\Models\ResourceType;

use Illuminate\Http\Request;


class ResourceTypeRepository extends BaseRepository
{
    public function __construct(ResourceType $m)
    {
        parent::__construct($m);
    }

    /*public function index(Request $request)
    {
        $query = ResourceType::withCount(['resources', 'serviceTypes']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $resourceTypes = $query->orderBy('name')->paginate(15);

        return response()->json($resourceTypes);
    }

    public function show(ResourceType $resourceType)
    {
        $resourceType->load(['resources', 'serviceTypes']);
        return response()->json($resourceType);
    }

    public function store(StoreResourceTypeRequest $request)
    {
        $resourceType = ResourceType::create($request->validated());

        return response()->json([
            'message' => 'Tipo de recurso creado exitosamente',
            'resource_type' => $resourceType
        ], 201);
    }

    public function update(UpdateResourceTypeRequest $request, ResourceType $resourceType)
    {
        $resourceType->update($request->validated());

        return response()->json([
            'message' => 'Tipo de recurso actualizado exitosamente',
            'resource_type' => $resourceType
        ]);
    }
    */

    public function destroy(ResourceType $resourceType)
    {
        if ($resourceType->resources()->exists() || $resourceType->serviceTypes()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el tipo de recurso porque tiene recursos o servicios asociados'
            ], 422);
        }

        $resourceType->delete();

        return response()->json([
            'message' => 'Tipo de recurso eliminado exitosamente'
        ]);
    }

    public function resources(ResourceType $resourceType, Request $request)
    {
        $query = $resourceType->resources()->with(['resourceType']);

        if ($request->has('active')) {
            $query->where('is_active', true);
        }

        $resources = $query->orderBy('name')->paginate(20);

        return response()->json($resources);
    }

    public function availability(ResourceType $resourceType, Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        $availableCount = $resourceType->availableResourcesCount(
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'available_count' => $availableCount,
            'total_resources' => $resourceType->resources()->count()
        ]);
    }
}
