<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('companies', App\Http\Controllers\CompanyController::class);
Route::resource('estados', App\Http\Controllers\EstadoController::class);
Route::resource('clasificaciones', App\Http\Controllers\ClasificacionController::class);
Route::resource('funciones', App\Http\Controllers\FuncionController::class);
Route::resource('personalPsa', App\Http\Controllers\PersonalPsaController::class);
Route::resource('partes', App\Http\Controllers\ParteController::class);