<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
{
    protected $model;
    private $relations;

    public function __construct(array $relations = ['brand', 'category'])
    {
        $this->model = new Product();
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

            $p = $this->model->create($data);

            DB::commit();
            return $this->successResponseCreate($p->load($this->relations));
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
            $p = $this->model->findOrFail($id);
            $requestData = $request->all();

            // Inicializar array de datos a actualizar
            $updateData = [];

            // Manejar campos regulares (excepto imagen)
            if (isset($requestData['data'])) {
                // Si viene en formato {data: {campo: valor}}
                foreach ($requestData['data'] as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $p->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            } else {
                // Si viene en formato plano {campo: valor}
                foreach ($requestData as $field => $value) {
                    if ($field !== 'image' && array_key_exists($field, $p->getAttributes())) {
                        $updateData[$field] = $value;
                    }
                }
            }

            // Manejar imagen por separado
            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($p->image) {
                    Storage::delete('public/products/' . $p->image);
                }
                $updateData['image'] = $this->uploadImage($request->file('image'));
            }

            // Manejar campo activo específicamente para asegurar tipo booleano
            if (isset($updateData['active'])) {
                $updateData['active'] = filter_var($updateData['active'], FILTER_VALIDATE_BOOLEAN);
            }

            // Actualizar el modelo solo con los campos proporcionados
            $p->update($updateData);

            DB::commit();
            return $this->successResponse($p->fresh()->load($this->relations));
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
            $p = $this->model->findOrFail($id);

            // Eliminar imagen asociada si existe
            if ($p->image) {
                Storage::delete('public/' . $p->image);
            }

            $p->delete();

            DB::commit();
            return $this->successResponse($p);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function changestatus($id)
    {
        try {
            $p = $this->model->findOrFail($id);
            $p->active = $p->active == 1 ? 0 : 1;
            $p->save();

            return $this->successResponse($p->fresh()->load($this->relations));
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
        $path = $image->storeAs('public/products', $fileName);

        // Retornar solo el nombre del archivo (sin ruta)
        return $fileName;
    }

    /**
     * Métodos adicionales para relaciones
     */
    public function getBrands()
    {
        try {
            $b = Brand::where('active', true)->get();
            return $this->successResponse($b);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getCategories()
    {
        try {
            $c = Category::where('active', true)->get();
            return $this->successResponse($c);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
