<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class BaseRepository extends ApiController
{
    protected $model;
    private $relations;

    public function __construct(Model $model, array $relations = [])
    {
        $this->model = $model;
        $this->relations = $relations;
    }

    public function with(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    public function all()
    {
        try {
            $query = $this->model->query();

            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function get($id)
    {
        try {
            $query = $this->model->query();

            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query->findOrFail($id));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($request)
    {
        try {
            $jsonData = $request->getContent(); // Obtener datos del cuerpo de la solicitud
            $modelData = json_decode($jsonData, true); // Deserializar JSON a array

            // Crear un nuevo modelo a partir de los datos recibidos            
            $this->model->fill($modelData);
            $this->model->save();

            //$this->cacheForget();
            return $this->successResponseCreate($this->model);
        } catch (\Exception $e) {

            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function update($request, $id)
    {
        try {
            $form = $request->all();

            $model = $this->model::findOrFail($id);

            $model->update($form['data']);
            //$this->cacheForget();

            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function delete($id)
    {
        try {
            $model = $this->model::findOrFail($id);

            $model->delete();
            //$this->cacheForget();

            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function changestatus($request, $id)
    {
        try {
            $model = $this->model::findOrFail($id);
            $model->activo = $model->activo == 1 ? 0 : 1;
            $model->save();

            //$this->cacheForget();
            return $this->successResponse($model);
        } catch (\Exception $e) {
            report($e);

            return $this->errorResponse($e);
        }
    }
}
