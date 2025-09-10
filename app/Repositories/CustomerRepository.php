<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerRepository extends  BaseRepository
{
    private $relations = ['locality'];
    public function __construct(Customer $m, array $relations = ['locality'])
    {
        parent::__construct($m, $relations);
    }

    public function all()
    {
        try {

            $roles = Auth::user()->roles()->get();


            $query = $this->model->query();
            // Si es vendedor de bebidas (rol ID 3), solo ve sus clientes
            if ($roles[0]->id == 3) {
                $query->where('created_by', Auth::id());
            }
            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
