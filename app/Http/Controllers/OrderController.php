<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Maatwebsite\Excel\Facades\Excel;


class OrderController extends Controller
{
    private $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->all();
    }

    public function show($id)
    {
        return $this->repository->get($id);
    }

    public function store(Request $request)
    {
        return $this->repository->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function updateOrder(Request $request, $id)
    {

        return $this->repository->updateOrder($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

    public function changestatus(Request $request, $id)
    {
        return $this->repository->changestatus($request, $id);
    }

    private function buildComandaPdf(Order $order): string
    {

        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format ' => [150, 297], // 80mm de ancho, alto automático
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
        /*
        $mpdf->showImageErrors = true;
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;
        */

        // Vista de la factura
        $html = view('invoices.remitoComanda', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png')
        ])->render();



        $mpdf->WriteHTML($html);

        return $mpdf->Output($this->comandaFileName($order), \Mpdf\Output\Destination::STRING_RETURN);
    }

    /**
     * Nombre público de la comanda: fecha_hora_direccion_importe.pdf
     * Ej: 2026-11-01_153045_av-corrientes-1234_15000.pdf
     */
    private function comandaFileName(Order $order): string
    {
        $address = $order->customer->address ?: $order->shipping_address ?: 'sin-direccion';

        return implode('_', [
            $order->order_date->format('Y-m-d'),
            $order->order_date->format('His'),
            Str::limit(Str::slug($address), 80, '') ?: 'sin-direccion',
            (int) round($order->total_amount),
        ]) . '.pdf';
    }

    private function comandaRelations(): array
    {
        return ['customer', 'items', 'payment', 'payment.paymentMethod'];
    }

    // Autenticado: devuelve el link público (no guarda nada en disco)
    public function generateRemitoComanda($id)
    {
        $order = Order::with($this->comandaRelations())->findOrFail($id);
        $fileName = $this->comandaFileName($order);

        return response()->json([
            'filename' => $fileName,
            'url' => url("/api/comanda/{$fileName}"),
        ]);
    }

    // Público: sin login. El PDF se genera al vuelo a partir del nombre.
    public function publicRemitoComanda($file)
    {
        $order = $this->findOrderByPublicFileName($file, $this->comandaRelations());

        return response($this->buildComandaPdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"',
        ]);
    }

    private function findOrderByPublicFileName(string $file, array $relations): Order
    {
        if (!preg_match('/^(\d{4}-\d{2}-\d{2})_(\d{6})_.+_\d+\.pdf$/', $file, $m)) {
            abort(404);
        }

        $orderDate = \Carbon\Carbon::createFromFormat('Y-m-d His', "{$m[1]} {$m[2]}");

        $order = Order::with($relations)
            ->where('order_date', $orderDate->format('Y-m-d H:i:s'))
            ->get()
            ->first(fn($o) => $this->comandaFileName($o) === $file);

        abort_unless($order, 404);

        return $order;
    }


    private function remitoRelations(): array
    {
        return ['customer', 'items', 'payment', 'payment.paymentMethod', 'paymentStatus', 'shipmentStatus'];
    }

    // Autenticado: devuelve el link público (no guarda nada en disco)
    public function generateRemito($id)
    {
        $order = Order::with($this->remitoRelations())->findOrFail($id);
        $fileName = $this->comandaFileName($order);

        return response()->json([
            'filename' => $fileName,
            'url' => url("/api/remito/{$fileName}"),
        ]);
    }

    // Público: sin login. El PDF se genera al vuelo a partir del nombre.
    public function publicRemito($file)
    {
        $order = $this->findOrderByPublicFileName($file, $this->remitoRelations());

        return response($this->buildRemitoPdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"',
        ]);
    }

    /**
     * Presupuesto: reutiliza el remito pero con los datos cargados en pantalla.
     * No guarda nada (ni orden, ni número, ni stock): arma una Order en memoria.
     */
    public function generateQuote(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'aditional' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $customer = Customer::with('locality')->findOrFail($data['customer_id']);
        $products = Product::withoutGlobalScope('order')
            ->whereIn('id', collect($data['items'])->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        $items = collect($data['items'])->map(function ($row) use ($products) {
            abort_unless($products->has($row['product_id']), 422, 'Producto inexistente en el presupuesto.');

            $item = new OrderItem([
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'total_price' => $row['quantity'] * $row['unit_price'],
            ]);
            $item->setRelation('product', $products[$row['product_id']]);

            return $item;
        });

        $subtotal = $items->sum('total_price');
        $aditional = (float) ($data['aditional'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);

        $order = new Order([
            'order_date' => now(),
            'order_number' => 'PRESUPUESTO',
            'customer_id' => $customer->id,
            'shipping_address' => $data['shipping_address'] ?? $customer->address,
            'quantity_products' => $items->count(),
            'subtotal' => $subtotal,
            'aditional' => $aditional,
            'discount_amount' => $discount,
            'total_amount' => $subtotal + $aditional - $discount,
            'notes' => $data['notes'] ?? null,
            'tenant_id' => Auth::user()->tenant_id,
        ]);
        $order->setRelation('customer', $customer);
        $order->setRelation('items', $items);
        $order->setRelation('payment', collect());
        $order->setRelation('tenant', Tenant::findOrFail(Auth::user()->tenant_id));

        $fileName = 'presupuesto_' . now()->format('Y-m-d_His') . '.pdf';

        return response($this->buildRemitoPdf($order, true), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    private function buildRemitoPdf(Order $order, bool $presupuesto = false): string
    {




        // Configuración de mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            //'format' => [150, 297], // Ancho 150mm, alto automático (como A4)
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 2,
            'margin_footer' => 5,
            'default_font_size' => 8,
            'default_font' => 'dejavusans',
            'orientation' => 'P'
        ]);

        // Vista del remito (primera copia)
        $html1 = view('invoices.remito', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png'),
            'copyText' => 'ORIGINAL', // Texto para identificar la copia
            'presupuesto' => $presupuesto,
        ])->render();


        // Vista del remito (segunda copia)
        $html2 = view('invoices.remito', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
            'logo' => public_path('logo.png'),
            'copyText' => 'DUPLICADO', // Texto para identificar la copia
            'presupuesto' => $presupuesto,
        ])->render();

        if ($presupuesto) {
            $mpdf->SetWatermarkText('PRESUPUESTO', 0.1);
            $mpdf->showWatermarkText = true;
        }

        // Agregar primera copia
        $mpdf->WriteHTML($html1);


        if ($order->tenant_id == 2 && !$presupuesto) // si es bebida hago 2 copias (el presupuesto lleva una sola)
        {

            $mpdf->WriteHTML('<div style="page-break-after: always;"></div>');
            // O si prefieres sin salto de página, con una línea divisoria:
            // $mpdf->WriteHTML('<hr style="border: 1px dashed #000; margin: 20px 0;">');

            $mpdf->WriteHTML($html2);
        }

        return $mpdf->Output($presupuesto ? 'presupuesto.pdf' : $this->comandaFileName($order), \Mpdf\Output\Destination::STRING_RETURN);
    }

    public function generateCustomerDeliveryReport(Request $request)
    {
        return $this->repository->generateCustomerDeliveryReport($request);
    }

    public function generateDeliveryReportExcel(Request $request)
    {
        return $this->repository->generateDeliveryReportExcel($request);
    }

    public function generateDeliveryReport(Request $request)
    {
        return $this->repository->generateDeliveryReport($request);
    }


    public function search(Request $request)
    {
        return $this->repository->search($request);
    }

    public function latest(Request $request)
    {
        return $this->repository->latest($request);
    }

    public function cancelOrder(Request $request, $id)
    {
        return $this->repository->cancelOrder($request, $id);
    }



    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Validar fechas si están presentes
            if ($startDate) {
                $request->validate([
                    'start_date' => 'date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                ]);
            }

            // Generar nombre de archivo
            $tenantId = Auth::user()->tenant_id;
            $fileName = 'ordenes_' . date('Y-m-d_His') . '.xlsx';

            // Configurar headers para descarga
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            return Excel::download(new OrdersExport($startDate, $endDate), $fileName, null, $headers);
        } catch (\Exception $e) {
            \Log::error('Error al exportar órdenes a Excel: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al exportar el archivo Excel',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
