<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ResourceRepository;
use App\Models\Resource;

class ResourceController extends Controller
{

    private $repository;

    public function __construct(ResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
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

    public function destroy(Resource $resource)
    {
        return $this->repository->destroy($resource);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }    

    public function availability(Resource $resource, Request $request)
    {
        return $this->repository->checkAvailability($resource, $request);
    }

    public function reservations(Resource $resource, Request $request)
    {
        return $this->repository->reservations($resource, $request);
    }

    public function toggleStatus(Resource $resource)
    {
        return $this->repository->toggleStatus($resource);
    }

    public function updateUsage(Resource $resource)
    {
        return $this->repository->updateUsage($resource);
    }
}