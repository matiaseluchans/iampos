<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    protected $model;
    private $relations;

    public function __construct(array $relations = ['tenant', 'roles'])
    {
        $this->model = new User();
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
            /*
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'tenant_id' => 'required|exists:tenants,id',
                'roles' => 'array',
                'roles.*' => 'exists:roles,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }*/

            $data = $request->except('image', 'password');
            $data['password'] = Hash::make($request->password);

            // Manejar la imagen si se proporcionó
            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
                \Log::info('Imagen subida:', ['nombre' => $data['image']]); // Para depuración
            }

            $user = $this->model->create($data);

            // Asignar roles si se proporcionaron
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            DB::commit();
            return $this->successResponseCreate($user->load($this->relations));
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
            $user = $this->model->findOrFail($id);
            $requestData = $request->all();

            // Depuración: registrar datos recibidos
            \Log::debug('Update User - Data Received:', [
                'all_data' => $request->all(),
                'roles_data' => $request->input('roles', []),
            ]);

            $updateData = $request->except('image', 'password', 'roles');

            // Manejar contraseña
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Manejar imagen
            if ($request->hasFile('image')) {
                if ($user->image) {
                    Storage::delete('public/users/' . $user->image);
                }
                $updateData['image'] = $this->uploadImage($request->file('image'));
                \Log::info('Imagen actualizada:', ['nombre' => $updateData['image']]); // Para depuración

            } elseif ($request->has('image') && $request->image === null) {
                if ($user->image) {
                    Storage::delete('public/users/' . $user->image);
                }
                $updateData['image'] = null;
            }

            // Actualizar datos básicos
            $user->update($updateData);

            // Manejar roles - versión robusta
            if ($request->has('roles')) {
                $rolesInput = $request->input('roles');

                // Convertir a array si es string JSON
                if (is_string($rolesInput)) {
                    $rolesInput = json_decode($rolesInput, true) ?: [];
                }

                // Asegurar que sea array
                $rolesArray = (array)$rolesInput;

                // Extraer IDs de roles
                $roleIds = [];
                foreach ($rolesArray as $role) {
                    if (is_numeric($role)) {
                        $roleIds[] = (int)$role;
                    } elseif (is_array($role) && isset($role['id'])) {
                        $roleIds[] = (int)$role['id'];
                    } elseif (is_object($role) && isset($role->id)) {
                        $roleIds[] = (int)$role->id;
                    }
                }

                // Filtrar IDs válidos
                $validRoleIds = array_filter(array_unique($roleIds));

                \Log::debug('Roles to sync:', ['role_ids' => $validRoleIds]);

                // Sincronizar roles
                $user->roles()->sync($validRoleIds);

                \Log::debug('Roles after sync:', [
                    'attached' => $user->roles->pluck('id')->toArray()
                ]);
            }

            DB::commit();

            return $this->successResponse($user->fresh()->load($this->relations));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating user roles: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->findOrFail($id);

            // Eliminar imagen asociada si existe
            if ($user->image) {
                Storage::delete('public/users/' . $user->image);
            }

            $user->delete();

            DB::commit();
            return $this->successResponse($user);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function changestatus($id)
    {
        try {
            $user = $this->model->findOrFail($id);
            $user->active = !$user->active;
            $user->save();

            return $this->successResponse($user->fresh()->load($this->relations));
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Método para subir imágenes de perfil
     */
    protected function uploadImage($image)
    {
        // Obtener nombre original y extensión
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();

        // Crear nombre único: nombre_original + uniqid + extensión
        $fileName = $originalName . '_' . uniqid() . '.' . $extension;

        // Guardar en storage/app/public/users con el nombre personalizado
        $path = $image->storeAs('public/users', $fileName);

        // Retornar solo el nombre del archivo (sin ruta)
        return $fileName;
    }

    /**
     * Métodos adicionales para relaciones
     */
    public function getTenants()
    {
        try {
            $tenants = Tenant::where('active', true)->get(['id', 'name']);
            return $this->successResponse($tenants);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getRoles()
    {
        try {
            $roles = Role::where('active', true)->get(['id', 'name']);
            return $this->successResponse($roles);
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function getListUsers()
    {
        try {                        
            return $this->successResponse($this->model::where('tenant_id', Auth::user()->tenant_id)->get());            
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
