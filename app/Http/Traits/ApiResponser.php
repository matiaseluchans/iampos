<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

trait ApiResponser
{

    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' =>  'Success',
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }

    protected function successResponseCreate($data, $message = null)
    {
        return response()->json([
            'status' =>  'Success',
            'message' => $message,
            'data' => $data,
            'code' => 201
        ], 201);
    }

    protected function errorResponse($ex, $message = null, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {

        if ($ex instanceof ValidationException) {
            foreach ($ex->errors() as $k => $v)
                $message = $v[0];
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
        } else {
            if (!$message) {
                if (isset($ex->errorInfo[2])) {
                    $message = $ex->errorInfo[2];
                    $defaultCode = $ex->errorInfo[1];
                } else {

                    $message = $ex->getMessage();
                }
            }

            $code = $ex->status ?? $code;

            if (\Config::get('app.debug')) {
                $message = "Error: " . $message;
                \Log::info($message);
            } else {
                \Log::info($message);
                $message = "Parece que esto no funciona";
            }
        }

        //Pulir este error ya que deberia ir code 204
        $errorTexto = "No query results for model";

        $posicion = strpos($message, $errorTexto);

        if ($posicion !== false) {
            $message = "No query results for model";
            $data = "[]";
            // $code = 204;
        } else {
            $data = null;
        }

        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }
}
