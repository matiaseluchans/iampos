<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;


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

    //Route::post('products/{id}', [App\Http\Controllers\ProductController::class, 'update']);
    Route::apiResource('products', App\Http\Controllers\ProductController::class);
    Route::put('products_changestatus/{id}', [App\Http\Controllers\ProductController::class, 'changestatus']);
    /*
        Route::post('products/{id}', [App\Http\Controllers\OrderController::class, 'update']);
    Route::apiResource('products', App\Http\Controllers\ProductController::class)->except(['update']);
     */
    Route::apiResource('brands', App\Http\Controllers\BrandController::class);
    Route::post('brands/{id}', [App\Http\Controllers\BrandController::class, "update"]);

    Route::apiResource('localities', App\Http\Controllers\LocalityController::class);
    Route::post('localities/{id}', [App\Http\Controllers\LocalityController::class, "update"]);
    Route::apiResource('statuses', App\Http\Controllers\StatusController::class);
    Route::post('statuses/{id}', [App\Http\Controllers\StatusController::class, "update"]);

    Route::apiResource('payment-methods', App\Http\Controllers\PaymentMethodController::class);
    Route::post('payment-methods/{id}', [App\Http\Controllers\PaymentMethodController::class, "update"]);

    Route::apiResource('shipment-statuses', App\Http\Controllers\ShipmentStatusController::class);
    Route::post('shipment-statuses/{id}', [App\Http\Controllers\ShipmentStatusController::class, "update"]);

    Route::apiResource('payment-statuses', App\Http\Controllers\PaymentStatusController::class);
    Route::post('payment-statuses/{id}', [App\Http\Controllers\PaymentStatusController::class, "update"]);


    Route::post('orders/{order}', [App\Http\Controllers\OrderController::class, 'update']);
    Route::post('orders-update/{order}', [App\Http\Controllers\OrderController::class, 'updateOrder']);
    Route::put('orders-cancel/{order}', [App\Http\Controllers\OrderController::class, 'cancelOrder']);
    Route::apiResource('orders', App\Http\Controllers\OrderController::class)->except(['update']);

    //reports
    Route::get('orders/remito/{id}', [App\Http\Controllers\OrderController::class, "generateRemito"]);
    Route::get('orders/remito-comanda/{id}', [App\Http\Controllers\OrderController::class, "generateRemitoComanda"]);
    Route::get('orders-delivery', [App\Http\Controllers\OrderController::class, "generateDeliveryReport"]);
    Route::get('orders-customers-delivery', [App\Http\Controllers\OrderController::class, "generateCustomerDeliveryReport"]);
    Route::get('orders-delivery-excel', [App\Http\Controllers\OrderController::class, "generateDeliveryReportExcel"]);
    Route::get('orders-search', [App\Http\Controllers\OrderController::class, "search"]);
    Route::get('orders-latest', [App\Http\Controllers\OrderController::class, "latest"]);

    Route::apiResource('roles', App\Http\Controllers\RoleController::class);
    Route::post('roles/{id}', [App\Http\Controllers\RoleController::class, "update"]);



    Route::put('users_changestatus/{id}', [App\Http\Controllers\UserController::class, 'changestatus']);

    Route::apiResource('users', App\Http\Controllers\UserController::class)/*->except(['update'])*/;
    Route::get('users/tenants', [App\Http\Controllers\UserController::class, 'getTenants']);
    Route::get('users/roles', [App\Http\Controllers\UserController::class, 'getRoles']);
    Route::get('users-list', [App\Http\Controllers\UserController::class, 'getListUsers']);

    Route::patch('users/{user}/toggle-active', [App\Http\Controllers\UserController::class, 'toggleActive']);


    Route::apiResource('warehouses', App\Http\Controllers\WarehouseController::class);
    Route::post('warehouses/{id}', [App\Http\Controllers\WarehouseController::class, "update"]);


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

    Route::apiResource('price-lists', App\Http\Controllers\PriceListController::class);
    Route::post('price-lists/{price_list}/products/sync', [App\Http\Controllers\PriceListController::class, 'syncProducts']);

    Route::prefix('price-list-products')->group(function () {
        Route::get('/', [App\Http\Controllers\PriceListProductController::class, 'index']);
        Route::post('/bulk-update', [App\Http\Controllers\PriceListProductController::class, 'bulkUpdate']);
        Route::get('/{priceListId}/{productId}', [App\Http\Controllers\PriceListProductController::class, 'getProductPrice']);
    });


    /** reservas */
    Route::get('users/{user}/reservations', [App\Http\Controllers\UserController::class, 'reservations']);
    
    // Resource Types
    Route::apiResource('resource-types', App\Http\Controllers\ResourceTypeController::class);
    Route::get('resource-types/{resourceType}/resources', [App\Http\Controllers\ResourceTypeController::class, 'resources']);
    Route::get('resource-types/{resourceType}/availability', [App\Http\Controllers\ResourceTypeController::class, 'availability']);
    
    // Service Types
    Route::apiResource('service-types', App\Http\Controllers\ServiceTypeController::class);
    Route::get('service-types/{serviceType}/availability', [App\Http\Controllers\ServiceTypeController::class, 'availability']);
    Route::get('service-types/{serviceType}/time-slots', [App\Http\Controllers\ServiceTypeController::class, 'timeSlots']);
    Route::get('service-types/{serviceType}/pricing-rules', [App\Http\Controllers\ServiceTypeController::class, 'pricingRules']);
    
    // Resources
    Route::apiResource('resources', App\Http\Controllers\ResourceController::class);
    Route::post('resources/{resource}/toggle-status', [App\Http\Controllers\ResourceController::class, 'toggleStatus']);
    Route::get('resources/{resource}/availability', [App\Http\Controllers\ResourceController::class, 'availability']);
    Route::get('resources/{resource}/reservations', [App\Http\Controllers\ResourceController::class, 'reservations']);
    Route::post('resources/{resource}/update-usage', [App\Http\Controllers\ResourceController::class, 'updateUsage']);
    
    // Reservations
    Route::apiResource('reservations', App\Http\Controllers\ReservationController::class);
    Route::post('reservations/{reservation}/cancel', [App\Http\Controllers\ReservationController::class, 'cancel']);
    Route::post('reservations/{reservation}/confirm', [App\Http\Controllers\ReservationController::class, 'confirm']);
    Route::get('reservations/{reservation}/quotations', [App\Http\Controllers\ReservationController::class, 'quotations']);
    Route::get('reservations/{reservation}/payments', [App\Http\Controllers\ReservationController::class, 'payments']);
    Route::post('reservations/check-availability', [App\Http\Controllers\ReservationController::class, 'checkAvailability']);
    Route::post('reservations/calculate-end-time', [App\Http\Controllers\ReservationController::class, 'calculateEndTime']);
    // Ruta adicional para cancelación
    Route::patch('reservations/{reservation}/cancel', [App\Http\Controllers\ReservationController::class, 'cancel'])
    ->name('reservations.cancel');

        // Ruta para actualizar solo el tiempo (desde el calendario)
        Route::patch('reservations/{reservation}/time', [App\Http\Controllers\ReservationController::class, 'updateTime'])
            ->name('reservations.update-time');
    
    // Quotations
    Route::apiResource('quotations', App\Http\Controllers\QuotationController::class);
    Route::post('quotations/{quotation}/accept', [App\Http\Controllers\QuotationController::class, 'accept']);
    Route::post('quotations/{quotation}/reject', [App\Http\Controllers\QuotationController::class, 'reject']);
    Route::post('quotations/{quotation}/mark-as-sent', [App\Http\Controllers\QuotationController::class, 'markAsSent']);
    Route::post('quotations/{quotation}/add-item', [App\Http\Controllers\QuotationController::class, 'addItem']);
    Route::delete('quotations/{quotation}/items/{itemId}', [App\Http\Controllers\QuotationController::class, 'removeItem']);
    Route::post('quotations/{quotation}/calculate-total', [App\Http\Controllers\QuotationController::class, 'calculateTotal']);
    Route::post('reservations/{reservation}/generate-quotation', [App\Http\Controllers\QuotationController::class, 'generateFromReservation']);
    
    // Pricing Rules
    Route::apiResource('pricing-rules', App\Http\Controllers\PricingRuleController::class);
    Route::post('pricing-rules/{pricingRule}/toggle-status', [App\Http\Controllers\PricingRuleController::class, 'toggleStatus']);
    Route::post('pricing-rules/{pricingRule}/test', [App\Http\Controllers\PricingRuleController::class, 'testRule']);
    Route::get('service-types/{serviceType}/pricing-rules', [App\Http\Controllers\PricingRuleController::class, 'serviceTypeRules']);
    
    // Payments
    Route::apiResource('payments', App\Http\Controllers\PaymentController::class);
    Route::post('payments/{payment}/mark-as-completed', [App\Http\Controllers\PaymentController::class, 'markAsCompleted']);
    Route::post('payments/{payment}/mark-as-failed', [App\Http\Controllers\PaymentController::class, 'markAsFailed']);
    Route::post('payments/{payment}/refund', [App\Http\Controllers\PaymentController::class, 'refund']);
    Route::get('reservations/{reservation}/payments', [App\Http\Controllers\PaymentController::class, 'reservationPayments']);
    Route::post('quotations/{quotation}/create-payment', [App\Http\Controllers\PaymentController::class, 'createFromQuotation']);

    // Reportes
    /*Route::get('reports/reservations-by-date', [ReportController::class, 'reservationsByDate']);
    Route::get('reports/revenue-by-service', [ReportController::class, 'revenueByService']);
    Route::get('reports/resource-utilization', [ReportController::class, 'resourceUtilization']);*/

});

// Rutas públicas para disponibilidad (si se desea)
Route::get('service-types/{serviceType}/availability', [App\Http\Controllers\ServiceTypeController::class, 'availability']);
Route::get('service-types/{serviceType}/time-slots', [App\Http\Controllers\ServiceTypeController::class, 'timeSlots']);
Route::post('reservations/check-availability', [App\Http\Controllers\ReservationController::class, 'checkAvailability']);


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


Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return response()->json(['message' => 'Cache cleared successfully']);
});
