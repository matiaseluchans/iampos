<?php

namespace App\Repositories;

use App\Models\Resource;

use Illuminate\Http\Request;


class ResourceRepository extends BaseRepository
{
    public function __construct(Resource $m)
    {
        parent::__construct($m);
    }

    public function index(Request $request)
    {
        $query = Resource::with(['resourceType'])->withCount(['reservations']);

        if ($request->has('resource_type_id')) {
            $query->where('resource_type_id', $request->resource_type_id);
        }

        /*if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }*/

        if ($request->has('shared')) {
            $query->where('is_shared', $request->shared);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $resources = $query->orderBy('name')->paginate(20);

        return response()->json($resources);
    }
    /*
    public function show(Resource $resource)
    {
        $resource->load(['resourceType', 'reservations.user']);
        return response()->json($resource);
    }

    

    public function store(StoreResourceRequest $request)
    {
        $resource = Resource::create($request->validated());

        return response()->json([
            'message' => 'Recurso creado exitosamente',
            'resource' => $resource
        ], 201);
    }

    public function update(UpdateResourceRequest $request, Resource $resource)
    {
        $resource->update($request->validated());

        return response()->json([
            'message' => 'Recurso actualizado exitosamente',
            'resource' => $resource
        ]);
    }
    */
    public function destroy(Resource $resource)
    {
        if ($resource->reservations()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el recurso porque tiene reservas asociadas'
            ], 422);
        }

        $resource->delete();

        return response()->json([
            'message' => 'Recurso eliminado exitosamente'
        ]);
    }

    public function availability(Resource $resource, Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'required_capacity' => 'required|integer|min:1'
        ]);

        $available = $resource->availability(
            $request->start_time,
            $request->end_time,
            $request->required_capacity
        );

        $availableCapacity = $resource->getAvailableCapacity(
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'available' => $available,
            'available_capacity' => $availableCapacity,
            'max_capacity' => $resource->capacity,
            'is_shared' => $resource->is_shared
        ]);
    }

    public function reservations(Resource $resource, Request $request)
    {
        $query = $resource->reservations()->with(['user', 'serviceType']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('upcoming')) {
            $query->where('start_time', '>', now());
        }

        $reservations = $query->orderBy('start_time')->paginate(15);

        return response()->json($reservations);
    }

    public function toggleStatus(Resource $resource)
    {
        $resource->update(['is_active' => !$resource->is_active]);

        return response()->json([
            'message' => 'Estado del recurso actualizado',
            'is_active' => $resource->is_active
        ]);
    }

    public function updateUsage(Resource $resource)
    {
        $resource->updateCurrentUsage();

        return response()->json([
            'current_usage' => $resource->current_usage,
            'available_capacity' => $resource->capacity - $resource->current_usage
        ]);
    }
}
