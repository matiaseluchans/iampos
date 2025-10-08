<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShipmentStatus;
use App\Models\PaymentStatus;
use App\Enums\StatusEnum;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeliveryReportExcelExport;
use App\Models\Stock;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $m, array $relations = ['customer', 'items', 'status', 'paymentStatus', 'shipmentStatus', 'orderType', 'payment', 'payment.paymentMethod'])
    {
        parent::__construct($m, $relations);
    }

    public function latest()
    {
        try {
            $days = config('app.orders_days');
            $query = $this->model->query();
            $relations = ['customer', 'status', 'paymentStatus', 'shipmentStatus', 'orderType', 'payment'];
            $query = $query->with($relations);
            $query->where('created_at', '>=', now()->subDays($days));

            $query->orderBy('order_date', 'desc');

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }

    public function save($request)
    {
        DB::beginTransaction();

        try {
            //Datos del Usuario
            $user = Auth::user();
            // Datos del formulario
            $formRequest = $request->all();

            $form = $formRequest;
            $items = $formRequest['items'];
            $errors = [];
            // Validación de los datos generales del pedido
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required',
            ], [
                "customer_id.required" => getMsg("required"),
            ]);

            // Validación de los ítems del pedido
            $validatorItems = Validator::make($request->input('items', []), [
                '*.product_id' => 'required',
                '*.quantity' => 'required',
            ], [
                '*.product_id.required' => getMsg("required"),
                '*.quantity.required' => getMsg("required"),
            ]);

            // Verificación de errores
            if ($validator->fails() || $validatorItems->fails()) {
                $errors = array_merge(
                    $validator->errors()->all(),
                    $validatorItems->errors()->all()
                );

                return $this->errorResponse(null, implode(', ', $errors));
            }
            /* comento validacion de stock
            foreach ($items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])->first();
                
                if ($stock->quantity < $item['quantity']) {
                    $errors[] = "No hay suficiente stock para el item ID {$item['product_id']}";
                }
            }
            */
            if (count($errors)) {
                return $this->errorResponse(null, implode(', ', $errors));
            }

            // Creación de la instancia del modelo 'order'
            $model = new $this->model;
            $sellerId = '';
            $sellerName = '';
            if (!$user->roles()->where('name', 'like', '%-admin%')->exists()) {
                $sellerId = $user->id;
                $sellerName = $user->name;
            } else {
                if ($user->tenant_id == 3) //petshop
                {
                    $sellerId = $user->id;
                    $sellerName = $user->name;
                } else {
                    $sellerId = $form['seller_id']['id'];
                    $sellerName = $form['seller_id']['name'];
                }
            }
            if (!isset($form['shipment_status_id'])) {
                //si es con envio
                if ($form['shipping']) {
                    // Si no se especifica el estado de envío, se asigna el penmdiente                    
                    $code = StatusEnum::PENDING;
                } else {
                    $code = StatusEnum::NOT_REQUIRED;
                }
                $shipmentStatus = ShipmentStatus::where('tenant_id', $user->tenant_id)
                    ->where('active', true)
                    ->where('code', $code)
                    ->first();
                $form['shipment_status_id'] = $shipmentStatus ? $shipmentStatus->id : null;
            }


            // Si no se especifica el estado de pago, se asigna el estado inicial
            if (!isset($form['payment_status_id'])) {
                $paymentStatus = PaymentStatus::where('tenant_id', $user->tenant_id)
                    ->where('active', true)
                    ->where('code', StatusEnum::PENDING)
                    ->first();
                $form['payment_status_id'] = $paymentStatus ? $paymentStatus->id : null; // Asignar 0 si no se encuentra el estado
            }
            // Asignación de los datos al modelo
            $model->fill([
                'order_date' => Carbon::now(),
                'delivery_date' => isset($form['delivery_date']) ?  Carbon::createFromFormat('d/m/Y', $form['delivery_date'])->format('Y-m-d') : null,
                'customer_id' => $form['customer_id'],
                //'customer_details', //ver de guardar los datos del cliente
                'shipping_address' => $form['shipping_address'],
                'shipping' => isset($form['shipping']) ? $form['shipping'] : 0,
                'status_id' => 1,
                'shipment_status_id' => $form['shipment_status_id'],
                'payment_status_id' => $form['payment_status_id'],
                'order_type_id' => 1,
                'quantity_products' => $form['quantity_products'],
                'subtotal' => $form['subtotal'],
                'aditional' => $form['aditional'],
                'discount_amount' => $form['discount_amount'],
                'total_amount' => $form['total_amount'],
                'total_cost' => $form['total_cost'],
                'total_profit' => ($form['total_profit']) ?? 0,
                'notes' => $form['notes'],
                'seller_id' => $sellerId,
                'seller_name' => $sellerName,
            ]);

            $model->save();

            // Registro de items con inserción masiva
            if (!empty($items)) {
                $data = array_map(function ($item) use ($model, $user) {
                    return [
                        'order_id' => $model->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost_price' => $item['unit_cost_price'],
                        'total_profit' => $item['total_profit'] ?? 0,
                        'total_price' => $item['total_price'],
                        'tenant_id' => $user->tenant_id,
                        'created_by' => $user->id,
                        'created_at' => now(),
                    ];
                }, $items);
                OrderItem::insert($data);
                /*
                //tengo q descontar del stock las cantidades de los items
                $ids = collect($items)->pluck('product_id')->toArray();

                //creo un update masivo                
                $sql = "UPDATE stocks SET quantity = CASE product_id ";
                foreach ($items as $item) {
                    $sql .= "WHEN {$item['product_id']} THEN GREATEST(0, quantity - {$item['quantity']}) ";
                }
                $sql .= "END WHERE product_id IN (" . implode(',', $ids) . ")";

                DB::statement($sql);
            }*/
                $productIds = collect($items)->pluck('product_id')->toArray();
                $previousStocks = Stock::whereIn('product_id', $productIds)
                    ->get()
                    ->keyBy('product_id');

                // 2. Ejecutar update masivo (eficiente)
                $sql = "UPDATE stocks SET quantity = CASE product_id ";
                foreach ($items as $item) {
                    $sql .= "WHEN {$item['product_id']} THEN GREATEST(0, quantity - {$item['quantity']}) ";
                }
                $sql .= "END WHERE product_id IN (" . implode(',', $productIds) . ")";
                DB::statement($sql);

                // 3. Registrar movimientos de stock
                $updatedStocks = Stock::whereIn('product_id', $productIds)->get();

                foreach ($items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];

                    $stock = $updatedStocks->where('product_id', $productId)->first();

                    if ($stock) {
                        // Registrar movimiento directamente
                        $stock->movements()->create([
                            'movement_type' => 'salida',
                            'quantity' => -$quantity,
                            'previous_quantity' => $previousStocks[$productId]->quantity,
                            'new_quantity' => $stock->quantity,
                            'notes' => "Orden - #{$model->order_number}",
                            'tenant_id' => $user->tenant_id,
                            'user_id' => $user->id
                        ]);
                    }
                }
            }

            DB::commit();


            return $this->successResponseCreate([
                'order' => $model,
                'items' => OrderItem::where('order_id', $model->id)->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }


    public function generateDeliveryReport($request)
    {
        /*
        $validator = Validator::make(
            $request->all(),
            [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ],
            [
                "start_date.required" => getMsg("required"),
                "start_date.date_format" => getMsg("date_format"),
                "end_date.required" => getMsg("required"),
                "end_date.date_format" => getMsg("date_format"),
            ]
        );

        if ($validator->fails()) {
            $errors = implode(', ', $validator->errors()->all());
            return $this->errorResponse(null, $errors);
        }
        */



        $ordersQuery = $this->model::select([
            'order_items.product_id',
            'products.name', // Nombre del producto
            DB::raw('SUM(order_items.quantity) as total_quantity')
        ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('shipping', 1);
        //     ->whereBetween('delivery_date', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);

        $deliveryStartDate = $request->input('start_date'); // Formato: Y-m-d
        $deliveryEndDate = $request->input('end_date');
        $dates = '';
        if ($deliveryStartDate && $deliveryEndDate) {
            $ordersQuery->whereBetween('delivery_date', [Carbon::parse($deliveryStartDate)->startOfDay(), Carbon::parse($deliveryEndDate)->endOfDay()]);
            $dates = Carbon::parse($deliveryStartDate)->format('d/m/Y') . ' - ' . Carbon::parse($deliveryEndDate)->format('d/m/Y');
        }

        if ($request->input('shipment_status_id')) {
            $ordersQuery->where('shipment_status_id', $request->input('shipment_status_id'));
        }
        if ($request->input('customers')) {
            $ordersQuery->whereIn('customer_id', $request->input('customers'));
        }
        if ($request->input('order_number')) {
            $ordersQuery->where('order_number', 'LIKE', '%' . $request->input('order_number') . '%');
        }
        if ($request->input('payment_status_id')) {
            $ordersQuery->where('payment_status_id', $request->input('payment_status_id'));
        }

        if ($request->input('sellers')) {
            $ordersQuery->where('seller_id', $request->input('sellers'));
        }

        $ordersQuery->orderBy('products.order', 'asc');
        $orders = $ordersQuery->groupBy('order_items.product_id', 'products.name')->get();

        //dd($orders);

        $totalQuantity = $orders->sum('total_quantity');

        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            //'format' => [120, 297], // 80mm de ancho, alto automático
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 2,
            'margin_footer' => 2,
            'default_font_size' => 8,
            'default_font' => 'Arial',
            'orientation' => 'P'
        ]);

        // Para maximizar compatibilidad con impresoras térmicas
        $mpdf->showImageErrors = true;
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        // Vista de la factura
        $html = view('invoices.delivery', [
            'dates' => $dates,
            'orders' => $orders,
            "user" => auth()->user(),
            'total' => $totalQuantity,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();


        $mpdf->WriteHTML($html);

        // Generar PDF
        return $mpdf->Output("orden_entrega.pdf", \Mpdf\Output\Destination::INLINE);
    }

    public function generateCustomerDeliveryReport($request)

    {
        $ordersQuery = $this->model::select([
            'orders.id',
            'customers.id',
            'customers.address',
            'localities.name as locality',
            DB::raw('SUM(order_items.total_price) as total_amount'),
            DB::raw('SUM(order_items.quantity) as total_quantity')
        ])
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('localities', 'localities.id', '=', 'customers.locality_id')
            /*->join('products', 'order_items.product_id', '=', 'products.id')*/
            ->where('shipping', 1);

        $deliveryStartDate = $request->input('start_date'); // Formato: Y-m-d
        $deliveryEndDate = $request->input('end_date');

        $dates = '';
        if ($deliveryStartDate && $deliveryEndDate) {
            $ordersQuery->whereBetween('delivery_date', [Carbon::parse($deliveryStartDate)->startOfDay(), Carbon::parse($deliveryEndDate)->endOfDay()]);
            $dates = Carbon::parse($deliveryStartDate)->format('d/m/Y') . ' - ' . Carbon::parse($deliveryEndDate)->format('d/m/Y');
        }
        if ($request->input('shipment_status_id')) {
            $ordersQuery->where('shipment_status_id', $request->input('shipment_status_id'));
        }
        if ($request->input('customers')) {
            $ordersQuery->whereIn('customer_id', $request->input('customers'));
        }
        if ($request->input('order_number')) {
            $ordersQuery->where('order_number', 'LIKE', '%' . $request->input('order_number') . '%');
        }
        if ($request->input('payment_status_id')) {
            $ordersQuery->where('payment_status_id', $request->input('payment_status_id'));
        }

        if ($request->input('sellers')) {
            $ordersQuery->where('seller_id', $request->input('sellers'));
        }

        //$orders = $ordersQuery->groupBy('customers.id', 'customers.address', 'order_items.product_id', 'products.name')->orderBy('customers.id', 'desc')->get();
        $orders = $ordersQuery->groupBy('orders.id', 'customers.id', 'customers.address', 'localities.name')->orderBy('orders.id', 'asc')->get();

        $totalQuantity = $orders->sum('total_quantity');

        $totalAmount = $orders->sum('total_amount');

        // Configuración de mPDF
        //$defaultConfig = (new ConfigVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            //'format' => [120, 297], // 80mm de ancho, alto automático
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 2,
            'margin_footer' => 2,
            'default_font_size' => 8,
            'default_font' => 'Arial',
            'orientation' => 'P'
        ]);

        // Para maximizar compatibilidad con impresoras térmicas
        $mpdf->showImageErrors = true;
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        // Vista de la factura
        $html = view('invoices.deliveryCustomers', [
            'dates' => $dates,
            'orders' => $orders,
            'totalProducts' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();

        $chunks = str_split($html, 500000); // Divide en trozos de ~500KB
        foreach ($chunks as $chunk) {
            $mpdf->WriteHTML($chunk);
        }
        //$mpdf->WriteHTML($html);

        // Generar PDF
        return $mpdf->Output("orden_entrega.pdf", \Mpdf\Output\Destination::INLINE);
    }



    public function generateDeliveryReportExcel($request)
    {
        $ordersQuery = $this->model::select([
            'order_items.product_id',
            'products.name as product_name', // Nombre del producto
            DB::raw('SUM(order_items.quantity) as total_quantity')
        ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('shipping', 1);

        $deliveryStartDate = $request->input('start_date');
        $deliveryEndDate = $request->input('end_date');
        $dates = '';

        if ($deliveryStartDate && $deliveryEndDate) {
            $ordersQuery->whereBetween('delivery_date', [
                Carbon::parse($deliveryStartDate)->startOfDay(),
                Carbon::parse($deliveryEndDate)->endOfDay()
            ]);
            $dates = Carbon::parse($deliveryStartDate)->format('d/m/Y') . ' - ' . Carbon::parse($deliveryEndDate)->format('d/m/Y');
        }

        if ($request->input('shipment_status_id')) {
            $ordersQuery->where('shipment_status_id', $request->input('shipment_status_id'));
        }
        if ($request->input('customers')) {
            $ordersQuery->whereIn('customer_id', $request->input('customers'));
        }
        if ($request->input('order_number')) {
            $ordersQuery->where('order_number', 'LIKE', '%' . $request->input('order_number') . '%');
        }
        if ($request->input('payment_status_id')) {
            $ordersQuery->where('payment_status_id', $request->input('payment_status_id'));
        }

        if ($request->input('sellers')) {
            $ordersQuery->where('seller_id', $request->input('sellers'));
        }

        $ordersQuery->orderBy('products.order', 'asc');
        $orders = $ordersQuery->groupBy('order_items.product_id', 'products.name')->get();

        $totalQuantity = $orders->sum('total_quantity');
        $user = auth()->user();

        // Generar y descargar el Excel
        return Excel::download(
            new DeliveryReportExcelExport($orders, $dates, $totalQuantity, $user),
            'reporte_entrega_productos.xlsx'
        );
    }

    public function search($request)
    {
        try {
            $query = $this->model->query();
            $relations = ['customer', 'status', 'paymentStatus', 'shipmentStatus', 'orderType', 'payment', 'payment.paymentMethod'];
            $query = $query->with($relations);

            $deliveryStartDate = $request->input('delivery_start_date'); // Formato: Y-m-d
            $deliveryEndDate = $request->input('delivery_end_date');

            $orderStartDate = $request->input('order_start_date'); // Formato: Y-m-d
            $orderEndDate = $request->input('order_end_date');

            if ($request->input('order_number')) {
                $query->where('order_number', 'LIKE', '%' . $request->input('order_number') . '%');
            }
            if ($request->input('payment_status_id')) {
                $query->where('payment_status_id', $request->input('payment_status_id'));
            }
            //dd($request->input('shipment_status_id'));
            if ($request->input('shipment_status_id')) {
                $query->where('shipment_status_id', $request->input('shipment_status_id'));
            }
            if ($request->input('shipping')) {
                $query->where('shipping', $request->input('shipping'));
            }
            if ($request->input('customers')) {
                $query->whereIn('customer_id', $request->input('customers'));
            }
            if ($deliveryStartDate && $deliveryEndDate) {
                $query->whereBetween('delivery_date', [Carbon::parse($deliveryStartDate)->startOfDay(), Carbon::parse($deliveryEndDate)->endOfDay()]);
            }
            if ($orderStartDate && $orderEndDate) {
                $query->whereBetween('order_date', [Carbon::parse($orderStartDate)->startOfDay(), Carbon::parse($orderEndDate)->endOfDay()]);
            }

            if ($request->input('sellers')) {
                $query->where('seller_id', $request->input('sellers'));
            }

            $roles = Auth::user()->roles()->get();
            // Si es vendedor de bebidas (rol ID 3), solo ve sus clientes
            if ($roles[0]->id == 3) {
                $query->where('seller_id', Auth::id());
            }

            $query->orderBy('order_date', 'desc');

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }



    public function updateOrder($request, $id)
    {
        DB::beginTransaction();

        try {
            // Datos del Usuario
            $user = Auth::user();
            // Datos del formulario
            $formRequest = $request->all();
            $form = $formRequest;
            $items = $formRequest['items'];
            $errors = [];

            // Validación de los datos generales del pedido
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required',
            ], [
                "customer_id.required" => getMsg("required"),
            ]);

            // Validación de los ítems del pedido
            $validatorItems = Validator::make($request->input('items', []), [
                '*.product_id' => 'required',
                '*.quantity' => 'required',
            ], [
                '*.product_id.required' => getMsg("required"),
                '*.quantity.required' => getMsg("required"),
            ]);

            // Verificación de errores
            if ($validator->fails() || $validatorItems->fails()) {
                $errors = array_merge(
                    $validator->errors()->all(),
                    $validatorItems->errors()->all()
                );
                return $this->errorResponse(null, implode(', ', $errors));
            }

            // Obtener la orden existente
            $model = $this->model::findOrFail($id);

            // 1. Eliminar todos los items anteriores y devolver el stock
            $oldItems = $model->items()->get();

            if ($oldItems->isNotEmpty()) {
                // Devolver stock
                $productIds = $oldItems->pluck('product_id')->toArray();
                $quantities = $oldItems->pluck('quantity', 'product_id')->toArray();

                $this->adjustStock($productIds, $quantities, 'increment', 'ajuste pre edicion', $model);

                // Eliminar items
                $model->items()->forceDelete();
            }

            // Actualizar datos básicos de la orden
            $sellerId = '';
            $sellerName = '';


            if (!$user->roles()->where('name', 'like', '%-admin%')->exists()) {
                $sellerId = $user->id;
                $sellerName = $user->name;
            } else {

                $sellerId = isset($form['seller_id']['id']) ? $form['seller_id']['id'] : $form['seller_id'];
                if ($model->seller_id != $sellerId)
                    $sellerName = $form['seller_id']['name'];
                else
                    $sellerName = $model->seller_name;
            }


            // Actualizar estados si no vienen en el request
            if (!isset($form['shipment_status_id'])) {
                $code = $form['shipping'] ? StatusEnum::PENDING : StatusEnum::NOT_REQUIRED;
                $shipmentStatus = ShipmentStatus::where('tenant_id', $user->tenant_id)
                    ->where('active', true)
                    ->where('code', $code)
                    ->first();
                $form['shipment_status_id'] = $shipmentStatus ? $shipmentStatus->id : null;
            }

            if (!isset($form['payment_status_id'])) {
                $paymentStatus = PaymentStatus::where('tenant_id', $user->tenant_id)
                    ->where('active', true)
                    ->where('code', StatusEnum::PENDING)
                    ->first();
                $form['payment_status_id'] = $paymentStatus ? $paymentStatus->id : null;
            }


            // Actualizar campos de la orden
            $model->fill([
                'delivery_date' => isset($form['delivery_date']) ? Carbon::createFromFormat('d/m/Y', $form['delivery_date'])->format('Y-m-d') : null,
                'customer_id' => $form['customer_id'],
                'shipping_address' => $form['shipping_address'],
                'shipping' => $form['shipping'] ?? 0,
                'shipment_status_id' => $form['shipment_status_id'],
                'payment_status_id' => $form['payment_status_id'],
                'quantity_products' => $form['quantity_products'],
                'subtotal' => $form['subtotal'],
                'discount_amount' => $form['discount_amount'],
                'total_amount' => $form['total_amount'],
                'total_cost' => $form['total_cost'],
                'total_profit' => $form['total_profit'] ?? 0,
                'notes' => $form['notes'],
                'seller_id' => $sellerId,
                'seller_name' => $sellerName,
            ]);

            $model->save();
            // 2. Crear nuevos items y descontar stock
            if (!empty($items)) {
                $data = array_map(function ($item) use ($model, $user) {
                    return [
                        'order_id' => $model->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost_price' => $item['unit_cost_price'],
                        'total_profit' => $item['total_profit'] ?? 0,
                        'total_price' => $item['total_price'],
                        'tenant_id' => $user->tenant_id,
                        'created_by' => $user->id,
                        'created_at' => now(),
                    ];
                }, $items);



                OrderItem::insert($data);

                // Descontar stock de los nuevos items
                $productIds = collect($items)->pluck('product_id')->toArray();
                $quantities = collect($items)->pluck('quantity', 'product_id')->toArray();

                $this->adjustStock($productIds, $quantities, 'decrement', 'ajuste pos Edicion', $model);
            }

            DB::commit();
            $this->cacheForget();

            return $this->successResponse([
                'order' => $model,
                'items' => $model->items()->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }

    // Método auxiliar para ajustar el stock (igual que antes)



    /*protected function adjustStock($productIds, $quantities, $operation)
    {
        if (empty($productIds)) return;

        $sql = "UPDATE stocks SET quantity = CASE product_id ";

        foreach ($productIds as $productId) {
            $quantity = $quantities[$productId];
            if ($operation === 'decrement') {
                $sql .= "WHEN {$productId} THEN GREATEST(0, quantity - {$quantity}) ";

                
            } else {
                $sql .= "WHEN {$productId} THEN quantity + {$quantity} ";
            }
        }

        $sql .= "END WHERE product_id IN (" . implode(',', $productIds) . ")";

        DB::statement($sql);
    }*/

    protected function adjustStock($productIds, $quantities, $operation, $movementType = 'ajuste', $model)
    {
        if (empty($productIds)) return;

        DB::transaction(function () use ($productIds, $quantities, $operation, $movementType, $model) {

            $stocks = Stock::whereIn('product_id', $productIds)->get();

            foreach ($stocks as $stock) {
                $productId = $stock->product_id;
                $quantityChange = $quantities[$productId] ?? 0;

                if ($quantityChange > 0) {
                    $previousQuantity = $stock->quantity;

                    // Calcular nueva cantidad
                    if ($operation === 'decrement') {
                        $newQuantity = max(0, $previousQuantity - $quantityChange);
                        $movementQuantity = -$quantityChange;
                    } else {
                        $newQuantity = $previousQuantity + $quantityChange;
                        $movementQuantity = $quantityChange;
                    }

                    // Actualizar stock
                    $stock->quantity = $newQuantity;
                    $stock->save();

                    // Registrar movimiento
                    $stock->movements()->create([
                        'movement_type' => $movementType,
                        'quantity' => $movementQuantity,
                        'previous_quantity' => $previousQuantity,
                        'new_quantity' => $newQuantity,
                        'notes' => "Orden - #{$model->order_number}",
                        'tenant_id' => $stock->tenant_id,
                        'user_id' => auth()->id()
                    ]);
                }
            }
        });
    }
    public function cancelOrder($request, $id)
    {
        DB::beginTransaction();

        try {
            // Datos del Usuario
            $user = Auth::user();
            // Datos del formulario
            $formRequest = $request->all();
            $form = $formRequest;
            // Obtener la orden existente
            $model = $this->model::findOrFail($id);

            // 1. Eliminar todos los items anteriores y devolver el stock
            $oldItems = $model->items()->get();

            if ($oldItems->isNotEmpty()) {
                // Devolver stock
                $productIds = $oldItems->pluck('product_id')->toArray();
                $quantities = $oldItems->pluck('quantity', 'product_id')->toArray();
                $this->adjustStock($productIds, $quantities, 'increment', 'ajuste por cancelacion', $model);
                // Eliminar items
                $model->items()->delete();
            }

            // Actualizar estados       
            $code = StatusEnum::CANCEL;
            $shipmentStatus = ShipmentStatus::where('tenant_id', $user->tenant_id)
                ->where('active', true)
                ->where('code', $code)
                ->first();
            $form['shipment_status_id'] = $shipmentStatus ? $shipmentStatus->id : null;

            if ($model->paymentStatus->code == StatusEnum::PAID || $model->paymentStatus->code == StatusEnum::PARTIAL_PAYMENT) {
                // Si la orden ya estaba pagada, se debe registrar un reembolso
                $code = StatusEnum::REFUND;
            } else {
                // Si no estaba pagada, se puede cancelar directamente
                $code = StatusEnum::CANCEL;
            }
            $paymentStatus = PaymentStatus::where('tenant_id', $user->tenant_id)->where('active', true)->where('code', $code)->first();
            // Asignar el estado de envío y pago            
            $form['payment_status_id'] = $paymentStatus ? $paymentStatus->id : null;

            // Actualizar campos de la orden
            $model->fill([
                'shipment_status_id' => $form['shipment_status_id'],
                'payment_status_id' => $form['payment_status_id'],
            ]);

            $model->save();

            DB::commit();
            $this->cacheForget();

            return $this->successResponse([
                'order' => $model,
                //'items' => $model->items()->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse($e);
        }
    }
}
