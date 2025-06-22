<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\RoleRepository;

class RoleController extends Controller
{
    private $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        if ($request->has('tenant_id')) {
            return $this->repository->getByTenant($request->tenant_id);
        }
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

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }
}
