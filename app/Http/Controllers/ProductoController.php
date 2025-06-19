<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ProductoRepository;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

class ProductoController extends ApiController
{
    protected $model;
    private $relations;

    public function __construct(array $relations = [])
    {
        $this->model = new Producto(); // Cambio importante: instanciamos el modelo
        $this->relations = $relations;
    }

    public function index()
    {
        try {
            $query = $this->model;

            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query::all());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        try {
            return $this->successResponse($this->model::findOrFail($id));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request) // Cambiado a Request type-hinted
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            // Procesar imagen si está presente
            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            $data["activo"] = ($data["activo"] == "true") ? 1 : ($data["activo"] == "true" ? 1 : $data["activo"]);
            // Crear el producto
            $producto = $this->model->create($data);

            DB::commit();
            return $this->successResponseCreate($producto);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function update(Request $request, $id) // Cambiado a Request type-hinted
    {
        DB::beginTransaction();
        try {
            $producto = $this->model::findOrFail($id);

            $producto->activo = $producto->activo == 1 ? 0 : 1;

            // Procesar imagen si está presente
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($producto->image) {
                    Storage::delete($producto->image);
                }
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            $producto->update($data);

            DB::commit();
            return $this->successResponse($producto);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $producto = $this->model::findOrFail($id);

            // Eliminar imagen asociada si existe
            if ($producto->image) {
                Storage::delete($producto->image);
            }

            $producto->delete();

            DB::commit();
            return $this->successResponse($producto);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function changestatus(Request $request, $id) // Cambiado a Request type-hinted
    {
        try {
            $producto = $this->model::findOrFail($id);
            $producto->activo = $producto->activo == 1 ? 0 : 1;
            $producto->save();

            return $this->successResponse($producto);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Método para subir imágenes
     */
    protected function uploadImage($image)
    {
        // Guardar en storage/app/public/productos
        $path = $image->store('public/productos');

        // Retornar la ruta relativa (sin 'public/')
        return str_replace('public/', '', $path);
    }
}
