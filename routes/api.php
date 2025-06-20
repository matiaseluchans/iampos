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

    return $request->user();
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
});
*/

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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::middleware('auth:sanctum')->get('/user', [App\Http\Controllers\AuthController::class, 'getUser']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('clientes', App\Http\Controllers\ClienteController::class);
    Route::apiResource('productos_categorias', App\Http\Controllers\ProductoCategoriaController::class);
    Route::apiResource('productos', App\Http\Controllers\ProductoController::class);
    Route::apiResource('marcas', App\Http\Controllers\MarcaController::class);
});


Route::resource('companies', App\Http\Controllers\CompanyController::class);
Route::resource('estados', App\Http\Controllers\EstadoController::class);
Route::resource('clasificaciones', App\Http\Controllers\ClasificacionController::class);
Route::resource('funciones', App\Http\Controllers\FuncionController::class);
