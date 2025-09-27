<?php

namespace App\Http\Controllers;

use App\Models\ResourceType;
use Illuminate\Http\Request;
use App\Repositories\ResourceTypeRepository;

class ResourceTypeController extends Controller
{
    private $repository;

    public function __construct(ResourceTypeRepository $repository)
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
        return $this->repository->destroy($resourceType);
    }

    public function resources(ResourceType $resourceType, Request $request)
    {
        return $this->repository->resources($resourceType, $request);
    }

    public function availability(ResourceType $resourceType, Request $request)
    {
        return $this->repository->checkAvailability($resourceType, $request);
    }
}