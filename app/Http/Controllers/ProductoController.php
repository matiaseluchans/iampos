<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\ProductoCategoria;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductoController extends ApiController
{
    protected $model;
    private $relations;

    public function __construct(array $relations = ['marca', 'categoria'])
    {
        $this->model = new Producto();
        $this->relations = $relations;
    }

    public function index()
    {
        try {
            $query = $this->model->with($this->relations);

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        try {
            return $this->successResponse(
                $this->model->with($this->relations)->findOrFail($id)
            );
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            /* $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'productos_categorias_id' => 'nullable|exists:productos_categorias,id',
                'marca_id' => 'nullable|exists:marcas,id',
                'codigo' => 'nullable|string|max:50|unique:productos,codigo',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
                'precio_compra' => 'nullable|numeric|min:0',
                'precio_venta' => 'nullable|numeric|min:0',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            */
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            //$data['activo'] = filter_var($request->input('activo', true), FILTER_VALIDATE_BOOLEAN);

            $producto = $this->model->create($data);

            DB::commit();
            return $this->successResponseCreate($producto->load($this->relations));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $producto = $this->model->findOrFail($id);
            $requestData = $request->all();

            // Inicializar array de datos a actualizar
            $updateData = [];

            // Manejar campos regulares (excepto imagen)
            if (isset($requestData['data'])) {
                // Si viene en formato {data: {campo: valor}}
                foreach ($requestData['data'] as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $producto->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            } else {
                // Si viene en formato plano {campo: valor}
                foreach ($requestData as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $producto->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            }

            // Manejar imagen por separado
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($producto->image) {
                    Storage::delete('public/productos/' . $producto->image);
                }
                $updateData['image'] = $this->uploadImage($request->file('image'));
            }

            // Manejar campo activo específicamente para asegurar tipo booleano
            if (isset($updateData['activo'])) {
                $updateData['activo'] = filter_var($updateData['activo'], FILTER_VALIDATE_BOOLEAN);
            }

            // Actualizar el modelo solo con los campos proporcionados
            $producto->update($updateData);

            DB::commit();
            return $this->successResponse($producto->fresh()->load($this->relations));
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $producto = $this->model->findOrFail($id);

            // Eliminar imagen asociada si existe
            if ($producto->image) {
                Storage::delete('public/' . $producto->image);
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

    public function changestatus($id)
    {
        try {
            $producto = $this->model->findOrFail($id);
            $producto->activo = $producto->activo == 1 ? 0 : 1;
            $producto->save();

            return $this->successResponse($producto->fresh()->load($this->relations));
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
        // Obtener nombre original y extensión
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        // Crear nombre único: nombre_original + uniqid + extensión
        $fileName = $originalName . '_' . uniqid() . '.' . $extension;

        // Guardar en storage/app/public/productos con el nombre personalizado
        $path = $image->storeAs('public/productos', $fileName);

        // Retornar solo el nombre del archivo (sin ruta)
        return $fileName;
    }

    /**
     * Métodos adicionales para relaciones
     */
    public function getMarcas()
    {
        try {
            $marcas = Marca::where('activo', true)->get();
            return $this->successResponse($marcas);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getCategorias()
    {
        try {
            $categorias = ProductoCategoria::where('activo', true)->get();
            return $this->successResponse($categorias);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
