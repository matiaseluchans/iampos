<?php
namespace App\Repositories;

use App\Models\Parte;
use DB;
use Carbon\Carbon;
use App\Models\ParteClasificacion;
use App\Models\ParteOficial;
use Illuminate\Support\Facades\Validator;


class ParteRepository extends  BaseRepository
{    
    public function __construct(Parte $parte)
    {
        parent::__construct($parte);
    }


    public function save($request)
    {
        DB::beginTransaction();

        try {
            // Validación de los datos de 'parte'
            $validator = Validator::make($request['parte'], [
                'fecha_parte' => 'required|date_format:d/m/Y H:i',
                'fecha_hecho' => 'required|date_format:d/m/Y H:i',
                'clasificacion_id' => 'required',
                'relato' => 'required',
            ], [
                "fecha_parte.required" => getMsg("required"),
                "fecha_parte.date_format" => getMsg("date_format"),
                "fecha_hecho.required" => getMsg("required"),
                "fecha_hecho.date_format" => getMsg("date_format"),
                "clasificacion_id.required" => getMsg("required"),
                "relato.required" => getMsg("required"),
            ]);

            // Validación de los oficiales
            $validatorOficiales = Validator::make($request['oficiales'], [
                '*.funcion_id' => 'required',
                '*.legajo' => 'required',
            ], [
                "*.funcion_id.required" => getMsg("required"),
                "*.legajo.required" => getMsg("required"),
            ]);

            if ($validator->fails() || $validatorOficiales->fails()) {
                $errors = implode(', ', $validator->errors()->all() + $validatorOficiales->errors()->all());
                return $this->errorResponse(null, $errors);
            }

            // Datos de la solicitud
            $formRequest = $request->all();
            $form = $formRequest['parte'];
            $formOficiales = $formRequest['oficiales'];

            // Creación de la instancia del modelo 'parte'
            $model = new $this->model;
            $estrutura_organica = '2.1.01.0.0';

            $model->fill([
                'estructura_organica_id' => $estrutura_organica,
                'estructura_organica' => strtoupper('UOSP EZEIZA'),
                'numero' => $model::getNumeroParte(),
                'numero_unidad' => $model::getNumeroUnidadParte($estrutura_organica),
                'anio' => date('Y'),
                'completado_por' => 'lquintero',
                'fecha_informe' => formatDate($form['fecha_parte']),
                'fecha_hecho' => formatDate($form['fecha_hecho']),
                'relato' => strtoupper($form['relato']),
                'observaciones' => (isset($form['observaciones']))? strtoupper($form['observaciones']):null,
                'estado_id' => 1,
            ]);

            $model->save();

            // Registro de clasificación en la tabla parte_clasificacion
            $parteClasificacion = new ParteClasificacion;
            $parteClasificacion->fill([
                'parte_id' => $model->id,
                'clasificacion_id' => $form['clasificacion_id']['id'],
            ]);
            $parteClasificacion->save();

            // Registro de oficiales con inserción masiva
            if (!empty($formOficiales)) {
                $data = array_map(function ($oficial) use ($model) {
                    return [
                        'parte_id' => $model->id,
                        'funcion_id' => $oficial['funcion_id']['id'],
                        'legajo' => $oficial['legajo'],
                        'documento' => $oficial['documento'],
                        'apellido' => $oficial['apellido'],
                        'nombres' => $oficial['nombres'],
                        'created_at' => now(),
                    ];
                }, $formOficiales);

                ParteOficial::insert($data);
            }

            // Confirmar la transacción
            DB::commit();

            $this->cacheForget();

            return $this->successResponseCreate([
                'parte' => $model,
                'clasificacion' => $parteClasificacion,
                'oficiales' => ParteOficial::where('parte_id', $model->id)->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
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