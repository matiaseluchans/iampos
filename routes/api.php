<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


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
    Route::apiResource('statuses', App\Http\Controllers\StatusController::class);
    Route::apiResource('payment-methods', App\Http\Controllers\PaymentMethodController::class);
    Route::apiResource('orders', App\Http\Controllers\OrderController::class);
    //reports
    Route::get('orders/remito/{id}', [App\Http\Controllers\OrderController::class, "generateRemito"]);
    Route::get('orders/remito-comanda/{id}', [App\Http\Controllers\OrderController::class, "generateRemitoComanda"]);
    Route::get('orders-delivery', [App\Http\Controllers\OrderController::class, "generateDeliveryReport"]);
    Route::get('orders-customers-delivery', [App\Http\Controllers\OrderController::class, "generateCustomerDeliveryReport"]);
    Route::get('orders-search', [App\Http\Controllers\OrderController::class, "search"]);
    Route::get('orders-latest', [App\Http\Controllers\OrderController::class, "latest"]);

    Route::apiResource('roles', App\Http\Controllers\RoleController::class);

    Route::apiResource('users', App\Http\Controllers\UserController::class);
    Route::get('users/tenants', [App\Http\Controllers\UserController::class, 'getTenants']);
    Route::get('users/roles', [App\Http\Controllers\UserController::class, 'getRoles']);
    Route::get('users-list', [App\Http\Controllers\UserController::class, 'getListUsers']);

    Route::patch('users/{user}/toggle-active', [App\Http\Controllers\UserController::class, 'toggleActive']);


    Route::apiResource('warehouses', App\Http\Controllers\WarehouseController::class);


    Route::get('stocks/summary', [App\Http\Controllers\StockController::class, 'summary']);

    Route::apiResource('stocks', App\Http\Controllers\StockController::class);
    Route::post('stocks/{stock}/movements', [App\Http\Controllers\StockController::class, 'recordMovement']);
    Route::get('stocks/{stock}/movements', [App\Http\Controllers\StockController::class, 'getMovements']);

    // Operaciones especiales
    Route::post('stocks/create-or-update', [App\Http\Controllers\StockController::class, 'createOrUpdate']);
    Route::post('stocks/transfer', [App\Http\Controllers\StockController::class, 'transfer']);
    Route::get('stocks/get-or-create', [App\Http\Controllers\StockController::class, 'getOrCreate']);

    // Reservas
    Route::post('stocks/{stock}/reserve', [App\Http\Controllers\StockController::class, 'reserve']);
    Route::post('stocks/{stock}/release-reservation', [App\Http\Controllers\StockController::class, 'releaseReservation']);

    // Consultas específicas
    Route::get('stocks/low-stock', [App\Http\Controllers\StockController::class, 'lowStock']);
    Route::get('stocks/by-product/{product}', [App\Http\Controllers\StockController::class, 'byProduct']);
    Route::get('stocks/by-warehouse/{warehouse}', [App\Http\Controllers\StockController::class, 'byWarehouse']);

    // Reportes
    Route::get('stocks/reports/movements', [App\Http\Controllers\StockController::class, 'movementsReport']);
    Route::get('stocks/reports/valuation', [App\Http\Controllers\StockController::class, 'valuationReport']);

    Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);
});


Route::get('/version', function () {
    try {
        //
        // 1. Obtener el último tag anotado
        //
        $tagProcess = new Process(['git', 'for-each-ref', '--sort=-creatordate', '--format=%(refname:short)', 'refs/tags']);
        $tagProcess->run();

        if (!$tagProcess->isSuccessful()) {
            throw new ProcessFailedException($tagProcess);
        }

        $tags = explode("\n", trim($tagProcess->getOutput()));
        $latestTag = $tags[0] ?? null;

        if (!$latestTag) {
            return response()->json(['error' => 'No se encontró ningún tag'], 404);
        }

        // Obtener info del tag
        $tagShowProcess = new Process(['git', 'show', $latestTag, '--no-patch']);
        $tagShowProcess->run();

        if (!$tagShowProcess->isSuccessful()) {
            throw new ProcessFailedException($tagShowProcess);
        }

        $tagOutput = $tagShowProcess->getOutput();

        preg_match('/^tag\s+(.*)$/m', $tagOutput, $tagMatch);
        preg_match('/^Tagger:\s+(.*)$/m', $tagOutput, $authorMatch);
        preg_match('/^Date:\s+(.*)$/m', $tagOutput, $dateMatch);
        preg_match('/\n\n(.*?)\n\n/s', $tagOutput, $tagMessageMatch);

        //
        // 2. Obtener el último commit de la rama actual
        //
        $commitProcess = new Process(['git', 'log', '-1', '--pretty=format:%H%n%an <%ae>%n%ad%n%B']);
        $commitProcess->run();

        if (!$commitProcess->isSuccessful()) {
            throw new ProcessFailedException($commitProcess);
        }

        $commitOutput = explode("\n", trim($commitProcess->getOutput()));

        return response()->json([
            'tag' => $tagMatch[1] ?? $latestTag,
            'tag_autor' => $authorMatch[1] ?? null,
            'tag_fecha' => $dateMatch[1] ?? null,
            'mensaje_tag' => trim($tagMessageMatch[1] ?? ''),

            'commit' => $commitOutput[0] ?? null,
            'commit_autor' => $commitOutput[1] ?? null,
            'commit_fecha' => $commitOutput[2] ?? null,
            'mensaje_commit' => trim(implode("\n", array_slice($commitOutput, 3))),
        ]);
    } catch (ProcessFailedException $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
