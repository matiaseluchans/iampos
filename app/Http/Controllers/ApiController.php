<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    use ApiResponser;

    /*public function swagger(Request $request)
    {
        $documentation = config("l5-swagger.default");
        $urlToDocs = config("app.prefix") . "/docs/" . config("l5-swagger.documentations.default.paths.docs_json");
        $config['operations_sort'] = null;
        $config['additional_config_url'] = null;
        $config['validator_url'] = null;
        $useAbsolutePath = true;
        return ResponseFacade::make(
            view('l5-swagger::index', [
                'documentation' => $documentation,
                'secure' => RequestFacade::secure(),
                'urlToDocs' => $urlToDocs,
                'operationsSorter' => $config['operations_sort'],
                'configUrl' => $config['additional_config_url'],
                'validatorUrl' => $config['validator_url'],
                'useAbsolutePath' => $useAbsolutePath,
            ]),
            200
        );
    }
    */

    protected function cacheAll($query)
    {
        $key = Auth::id() . class_basename($query->getModel());


        if (\Config::get("cache.activated")) {
            if (Cache::has($key)) {
                // Si los datos están en caché, devolverlos
                return Cache::get($key);
            } else {
                // Si los datos no están en caché, ejecutar la consulta
                $data = $query->get();

                $days = \Config::get("cache.days");

                // Guardar los datos en caché por un tiempo determinado
                Cache::put($key, $data, 60 * 24 * $days); // 60 minutos * 24 horas, X días de caché

                return $data;
            }
        } else {
            // Si la cache no está activada, ejecutar la consulta directamente
            return $query->get();
        }
    }

    protected function cacheForget()
    {
        if (\Config::get("cache.activated")) {
            $key = Auth::id() . class_basename($this->model);
            Cache::forget($key);
        }
    }

    // Método para limpiar cache por clave específica
    protected function cacheForgetByKey($key)
    {
        if (\Config::get("cache.activated")) {
            Cache::forget($key);
        }
    }

    // Método para obtener la clave de cache actual
    protected function getCacheKey($query = null)
    {
        $model = $query ? $query->getModel() : $this->model;
        return Auth::id() . class_basename($model);
    }
}
