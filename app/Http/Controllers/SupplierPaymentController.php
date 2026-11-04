<?php

namespace App\Http\Controllers;

use App\Repositories\SupplierPaymentRepository;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private $repository;

    public function __construct(SupplierPaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }
}
