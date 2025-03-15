<?php
namespace App\Repositories;

use App\Models\Estado;

class EstadoRepository extends  BaseRepository
{    
    public function __construct(Estado $estado)
    {
        parent::__construct($estado);
    }
/*
    public function all()
    {
        try {
            $query = $this->model;

            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($this->cacheAll($query));
            //return $this->successResponse($query);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function get($id)
    {
        try {
            return $this->successResponse($this->model::findOrFail($id));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($request)
    {
        //DB::setDefaultConnection(getDb());
        DB::beginTransaction();


        try {
            $validator = $request->validate([                
                'detalle' => 'required|string|max:500',
            ], [                
                "detalle.required" => getMsg("required"),
                "detalle.max" => getMsg("max")
            ]);

            $model = new $this->model;            
            $model->detalle = $request->detalle;
            $model->activo = isset($request->activo) ? $request->activo : 1;

            $model->save();

            DB::commit();
          
            $this->cacheForget();
            return $this->successResponseCreate($model);
        } catch (\Exception $e) {
            dd($e);
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function update($request, $id)
    {        
        DB::beginTransaction();

        try {
            //dd($request);
            $validator = $request->validate([                
                'data.detalle' => 'required|string|max:500',
            ], [                
                "detalle.required" => getMsg("required"),
                "detalle.max" => getMsg("max")
            ]);

            $form = $request->all();
        
            $model = $this->model::findOrFail($id);
            $model->update($form['data']);
            DB::commit();
            $this->cacheForget();
            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function delete($id)
    {        
        DB::beginTransaction();

        try {
            $model = $this->model::findOrFail($id);

            $model->delete();
            DB::commit();
            $this->cacheForget();
            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }



    public function changestatus($request, $id)
    {        
        DB::beginTransaction();
        try {
            $model = $this->model::findOrFail($id);
            $model->activo = $model->activo == 1 ? 0 : 1;
            $model->save();
            DB::commit();
            $this->cacheForget();
            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    */
}