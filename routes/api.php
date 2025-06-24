<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ]);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    // Revoca el token usado en la petición actual
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Successfully logged out']);
});
*/

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);




Route::middleware('auth:sanctum')->get('/validate-token', [App\Http\Controllers\AuthController::class, 'validateToken']);
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
//Route::middleware('auth:sanctum')->get('/user', [App\Http\Controllers\AuthController::class, 'getUser']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\AuthController::class, 'getUser']);
    Route::apiResource('tenants', App\Http\Controllers\TenantController::class);
    Route::apiResource('customers', App\Http\Controllers\CustomerController::class);
    Route::apiResource('categories', App\Http\Controllers\CategoryController::class);
    Route::apiResource('products', App\Http\Controllers\ProductController::class);
    Route::apiResource('brands', App\Http\Controllers\BrandController::class);
    Route::apiResource('localities', App\Http\Controllers\LocalityController::class);

    Route::apiResource('roles', App\Http\Controllers\RoleController::class);

    Route::apiResource('users', App\Http\Controllers\UserController::class);
    Route::get('users/tenants', [App\Http\Controllers\UserController::class, 'getTenants']);
    Route::get('users/roles', [App\Http\Controllers\UserController::class, 'getRoles']);
    Route::patch('users/{user}/toggle-active', [App\Http\Controllers\UserController::class, 'toggleActive']);
});
