<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
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
        $key = class_basename($query);

        if (\Config::get("cache.activated")) { //si el uso de la cache esta activa
            if (Cache::has($key)) {
                // Si los datos están en caché, devolverlos
                return Cache::get($key);
            } else {
                // Si los datos no están en caché, consultar la tabla
                $data = $query::all();

                $days = \Config::get("cache.days");

                // Guardar los datos en caché por un tiempo determinado (puedes ajustar el tiempo)
                Cache::put($key, $data, 60 * 24 * $days); // 60 minutos * 24 horas, 7 dias de caché

                return $data;
            }
        } else {
            $data = $query::all();
            return $data;
        }
    }
    protected function cacheForget()
    {
        if (\Config::get("cache.activated")) { //si el uso de la cache esta activa
            Cache::forget(class_basename($this->model));
        }
    }
}
